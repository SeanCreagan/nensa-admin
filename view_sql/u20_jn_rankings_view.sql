CREATE OR REPLACE VIEW MEMBER_SEASON_U20_JN_RANKINGS AS
SELECT s.season as 'Season', e.ussa_num as 'USSA_num', 
        m.nensa_num as 'NENSA', e.full_name as 'Athletes_Name', 
        m.sex as 'Sex', s.club_name as 'Club_Name', s.age_group as 'Age_Group',
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
            LIMIT 1,1)+(SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id and USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 2,1)+(SELECT USSA_Result
            FROM RACE_RESULTS
            WHERE member_season_id=s.id and USSA_Result <> 0
            GROUP BY USSA_Result HAVING COUNT(*) > 0
            ORDER BY USSA_Result ASC
            LIMIT 2,1))/4) as "Avg_4_Best"
FROM 
    MEMBER_SEASON s
        INNER JOIN 
    RACE_RESULTS e ON e.member_season_id = s.id
        INNER JOIN 
    MEMBER_SKIER m ON m.member_id = s.member_id
WHERE e.USSA_RESULT <> 0 AND (s.age_group='U20' OR s.age_group='U18')
GROUP BY s.id;

