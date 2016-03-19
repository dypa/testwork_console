<?php
namespace Testwork;

use SpaceWeb\Quest\QuestAbstract;

class DataSource extends QuestAbstract
{
    public function countWithDocuments($startDate, $endDate)
    {
        $sth = $this->getDb()->prepare('
            SELECT 
                COUNT(*) AS `count`,
                SUM(p.amount) AS amount
            FROM payments AS p
            INNER JOIN documents AS d ON d.entity_id = p.id
            WHERE
                p.create_ts >= :startDate
                AND
                p.create_ts <= :endDate
        ');
        $sth->bindParam(':startDate', $startDate);
        $sth->bindParam(':endDate', $endDate);
        $sth->execute();

        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function countWithoutDocuments($startDate, $endDate)
    {
        $sth = $this->getDb()->prepare('
            SELECT 
                COUNT(*) AS `count`,
                SUM(p.amount) AS amount
            FROM payments AS p
            LEFT JOIN documents AS d ON d.entity_id = p.id
            WHERE
                d.entity_id IS NULL
                AND
                p.create_ts >= :startDate
                AND
                p.create_ts <= :endDate
        ');
        $sth->bindParam(':startDate', $startDate);
        $sth->bindParam(':endDate', $endDate);
        $sth->execute();

        return $sth->fetch(\PDO::FETCH_ASSOC);
    }
}
