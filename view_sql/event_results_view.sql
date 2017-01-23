CREATE OR REPLACE VIEW EVENT_RESULTS AS
SELECT 
      e.event_name as 'Event', e.season as 'Season', 
      m.nensa_num as 'NENSA', s.ussa_num as 'USSA', 
      r.Finish_Place as 'Finish', r.Full_Name as 'Name', 
      r.Birth_Year as 'Birth Year', r.Division as 'Division', 
      m.age_group as 'Age Group', m.club_name as 'Club', 
      r.Race_Time as 'Race Time', r.Race_Points as 'Points',
      r.USSA_Result as 'USSA Result'
FROM 
    Race_Results r
        INNER JOIN
    RACE_EVENT e ON e.event_id = r.event_id
        LEFT JOIN
    MEMBER_SEASON m ON m.id = r.member_season_id
    	LEFT JOIN 
    MEMBER_SKIER s ON s.member_id = m.member_id
WHERE r.Finish_Place<>0;


