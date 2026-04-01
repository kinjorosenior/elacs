import React, { useEffect, useState } from "react";

function ActivityTimeline(){

  const [activities, setActivities] = useState([]);

  useEffect(()=>{

    fetch("http://localhost/elacs/backend/index.php?request=activity")
    .then(res=>res.json())
    .then(data=>{
      setActivities(Array.isArray(data)?data:[])
    })
    .catch(err=>console.error(err))

  },[]);

  return (

    <div style={{
      marginTop:"30px",
      background:"#1e293b",
      padding:"20px",
      borderRadius:"8px"
    }}>

      <h3>Recent Activity</h3>

      {activities.length === 0 && <p>No activity yet</p>}

      {activities.map((a,index)=>(

        <div key={index} style={{
          marginTop:"15px",
          borderBottom:"1px solid #334155",
          paddingBottom:"10px"
        }}>

          <strong>{a.full_name}</strong><br/>

         {a.status === "checkin" ? "Checked In" : "Checked Out"} — 
          {a.device_type} ({a.model})<br/>

          <small>{a.checkin_time}</small>

        </div>

      ))}

    </div>

  );

}

export default ActivityTimeline;