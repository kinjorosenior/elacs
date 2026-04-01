
import { useState, useEffect } from "react";
import AdminLayout from "../layouts/AdminLayout";

export default function Students(){
  const [showModal,setShowModal] = useState(false);
  const [editModal, setEditModal] = useState(false);
  const [students,setStudents] = useState([]);
  const [student,setStudent] = useState({
    id: "",
    student_id: "",
    full_name: "",
    department: "",
    year_of_study: "",
    phone: "",
    email: ""
  });

  const loadStudents = () => {
    fetch("http://localhost/elacs/backend/index.php?request=get_students")
      .then(res=>res.json())
      .then(data=>setStudents(Array.isArray(data)?data:[]));
  };

  useEffect(()=>{
    loadStudents();
  },[]);

  function handleChange(e){
    setStudent({...student,[e.target.name]:e.target.value});
  }

  function openEdit(studentData) {
    setStudent(studentData);
    setEditModal(true);
  }

  function submitStudent(e){
    e.preventDefault()
    const isEdit = !!student.id;
    const url = "http://localhost/elacs/backend/index.php?request=" + (isEdit ? "update_student" : "create_student");
    
    fetch(url,{
      method:"POST",
      headers:{
        "Content-Type":"application/json"
      },
      body:JSON.stringify(isEdit ? {id: student.id, ...student} : student)
    })
      .then(res=>res.json())
      .then(()=>{
        alert(isEdit ? "Student updated" : "Student registered successfully")
        setShowModal(false);
        setEditModal(false);
        setStudent({id: "", student_id: "", full_name: "", department: "", year_of_study: "", phone: "", email: ""});
        loadStudents();
      })
      .catch(()=>{
        alert("Failed to update student")
      })
  }

  const toggleStudentStatus = (id, currentStatus) => {
    const action = currentStatus === 'active' ? 'Deactivate' : 'Activate';
    if (!confirm(`${action} this student?`)) return;
    fetch("http://localhost/elacs/backend/index.php?request=" + (currentStatus === 'active' ? 'deactivate_student' : 'activate_student'),{
      method:"POST",
      headers:{
        "Content-Type":"application/json"
      },
      body:JSON.stringify({id})
    })
      .then(()=>{
        alert(`Student ${action.toLowerCase()}d`);
        loadStudents();
      });
  };

  return(
    <AdminLayout>
      <div className="students-container">
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "2rem" }}>
          <h2>Students</h2>
          <button onClick={()=>setShowModal(true)} className="btn success">
            + Add Student
          </button>
        </div>

        <table className="admin-table">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Name</th>
              <th>Department</th>
              <th>Year</th>
              <th>Contact</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {students.length === 0 && (
              <tr><td colSpan="7">No students found</td></tr>
            )}
            {students.map(s=>( 
              <tr key={s.id}>
                <td>{s.student_id}</td>
                <td>{s.full_name}</td>
                <td>{s.department}</td>
                <td>{s.year_of_study}</td>
                <td>{s.phone}</td>
                <td>
                  <span className={`status ${s.status}`}>
                    {s.status || 'Active'}
                  </span>
                </td>
                <td>
                  <button onClick={() => openEdit(s)} className="btn">Edit</button>
                  <button onClick={()=>toggleStudentStatus(s.id, s.status)} className="btn danger">
                    {s.status === 'active' || !s.status ? 'Deactivate' : 'Activate'}
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {showModal && (
          <div className="modal-overlay">
            <div className="modal">
              <h3>Add New Student</h3>
              <form onSubmit={submitStudent}>
                <input name="student_id" placeholder="Student ID *" value={student.student_id} onChange={handleChange} required />
                <input name="full_name" placeholder="Full Name *" value={student.full_name} onChange={handleChange} required />
                <input name="department" placeholder="Department" value={student.department} onChange={handleChange} />
                <input name="year_of_study" placeholder="Year of Study" value={student.year_of_study} onChange={handleChange} />
                <input name="phone" placeholder="Phone Number" value={student.phone} onChange={handleChange} />
                <input name="email" placeholder="Email" value={student.email} onChange={handleChange} />
                <button type="submit" className="btn success">Register Student</button>
              </form>
              <button onClick={()=>setShowModal(false)} className="btn">Close</button>
            </div>
          </div>
        )}

        {editModal && (
          <div className="modal-overlay">
            <div className="modal">
              <h3>Edit Student</h3>
              <form onSubmit={submitStudent}>
                <input name="student_id" placeholder="Student ID *" value={student.student_id} onChange={handleChange} required />
                <input name="full_name" placeholder="Full Name *" value={student.full_name} onChange={handleChange} required />
                <input name="department" placeholder="Department" value={student.department} onChange={handleChange} />
                <input name="year_of_study" placeholder="Year of Study" value={student.year_of_study} onChange={handleChange} />
                <input name="phone" placeholder="Phone Number" value={student.phone} onChange={handleChange} />
                <input name="email" placeholder="Email" value={student.email} onChange={handleChange} />
                <button type="submit" className="btn success">Update Student</button>
              </form>
              <button onClick={() => { setEditModal(false); setStudent({}); }} className="btn">Close</button>
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  );
}

