CREATE OR REPLACE VIEW MEMBER_SEASON_U16_JN_RANKINGS AS
SELECT s.season as 'Season', m.nensa_num as 'NENSA', m.first as 'First_Name', m.last as 'Last_Name', 
        m.sex as 'Sex', e.Division as 'Division', s.age_group as 'Age_Group',
        COUNT(e.USSA_Result) as '#_Races',
        MIN(e.USSA_Result) as 'Best_Race_Result',
        (SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id AND USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 0,1) as 'Best_USSA_Result',
        (SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id and USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 1,1) as '2ndBest_USSA_Result',
        (SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id and USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 2,1) as '3rdBest_USSA_Result',
        (((SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id AND USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 0,1)+(SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id and USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 1,1))/2) as "Avg_2_Best",
        (((SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id AND USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 0,1)+(SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id and USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 1,1))+(SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id and USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 2,1)/3) as "Avg_3_Best"
FROM 
    MEMBER_SEASON s
        INNER JOIN 
    RACE_RESULTS e ON e.member_season_id = s.id
        INNER JOIN 
    MEMBER_SKIER m ON m.member_id = s.member_id
WHERE e.USSA_RESULT <> 0 AND s.age_group='U16' 
GROUP BY s.id;

CREATE OR REPLACE VIEW MEMBER_SEASON_RANKINGS AS
SELECT `Season`, `NENSA`, `First Name`, `Sex`, `Last Name`, `Division`, `Age Group`, `# Races`, 
`Best Race Result`, `Best USSA Result`, `2ndBest USSA Result`, `3rdBest USSA Result`,
(Best_USSA_Result+2ndBest_USSA_Result)/2 AS 'Avg Top 2', 
(Best_USSA_Result+2ndBest_USSA_Result+3rdBest_USSA_Result)/3 AS 'Avg Top 3'
FROM MEMBER_SEASON_TOP_RESULTS;