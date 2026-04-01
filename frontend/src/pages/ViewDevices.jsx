import React,{useEffect,useState} from "react";

function ViewDevices(){

  const [devices,setDevices] = useState([]);

  useEffect(()=>{

    fetch("http://localhost/elacs/backend/api/devices/read.php")
      .then(res=>res.json())
      .then(data=>{
        if(Array.isArray(data)){
          setDevices(data);
        }else{
          setDevices([]);
        }
      })
      .catch(err=>{
        console.error(err);
        setDevices([]);
      });

  },[]);

  return(

    <div>

      <h2>All Devices</h2>

      <table>

        <thead>
          <tr>
            <th>Brand</th>
            <th>Model</th>
            <th>Serial</th>
            <th>Student</th>
          </tr>
        </thead>

        <tbody>

          {devices.length === 0 && (
            <tr>
              <td colSpan="4">No devices found</td>
            </tr>
          )}

          {devices.map(d=>(

            <tr key={d.id}>

              <td>{d.brand}</td>
              <td>{d.model}</td>
              <td>{d.serial_number}</td>
              <td>{d.student_id}</td>

            </tr>

          ))}

        </tbody>

      </table>

    </div>

  );

}

export default ViewDevices;