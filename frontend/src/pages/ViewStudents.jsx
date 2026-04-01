import React,{useEffect,useState} from "react";

function ViewStudents(){

  const [students,setStudents] = useState([]);

  useEffect(()=>{

    fetch("http://localhost/elacs/backend/api/students/read.php")
      .then(res=>res.json())
      .then(data=>setStudents(data));

  },[]);

  return(

    <div>

      <h2>All Students</h2>

      <table>

        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Year</th>
          </tr>
        </thead>

        <tbody>

          {students.map(s=>(

            <tr key={s.id}>

              <td>{s.student_id}</td>

              <td>{s.full_name}</td>

              <td>{s.department}</td>

              <td>{s.year_of_study}</td>

            </tr>

          ))}

        </tbody>

      </table>

    </div>

  );

}

export default ViewStudents;