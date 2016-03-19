<?php
namespace Testwork;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\Table;

class Command extends BaseCommand
{
    protected function configure()
    {
        $this->setName('testwork');
        $this->setDescription(
            'утилита вычисляющая количество и сумму платежей для которых сформированы и не сформированы документы'
        );
        $this->addOption(
            'without-documents',
            null,
            InputOption::VALUE_NONE,
            'Выводить сумму платежей для которых не сформированы документы'
        );
        $this->addOption(
            'with-documents',
            null,
            InputOption::VALUE_NONE,
            'Выводить сумму платежей для которых сформированы документы'
        );

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataSource = new DataSource();
        $helper = $this->getHelper('question');
        $table = new Table($output);

        if (!$input->getOption('without-documents') && !$input->getOption('with-documents')) {
            throw new \LogicException('Требуется указать хотя бы одну из опций');
        }

        $question = new Question('Please enter start date: [2015-07-20] ', '2015-07-20');
        $startDate = $this->validateDate($helper->ask($input, $output, $question));
        $question = new Question('Please enter end date: [2015-07-20] ', '2015-11-01');
        $endDate = $this->validateDate($helper->ask($input, $output, $question));
        unset($question);

        $tableRows = [];
        if ($input->getOption('with-documents')) {
            $result = $dataSource->countWithDocuments($startDate, $endDate);
            $tableRows[] = ['сформированы документы' , $result['count'], $result['amount']];
        }
        if ($input->getOption('without-documents')) {
            $result = $dataSource->countWithoutDocuments($startDate, $endDate);
            $tableRows[] = ['не сформированы документы' , $result['count'], $result['amount']];
        }

        $table->setHeaders(['Состояние документов', 'Count', 'Amount']);
        $table->setRows($tableRows);
        $table->render();
    }

    private function validateDate($string)
    {
        $datetime = \DateTime::createFromFormat('Y-m-d', $string);
        if (!$datetime) {
            throw new \InvalidArgumentException('Используйте дату в формате Y-m-d');
        }

        return $datetime->format('Y-m-d');
    }
}
