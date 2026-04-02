import { useState } from "react";
import AdminLayout from "../layouts/AdminLayout";
import "../styles/checkin.css";

export default function CheckIn() {
  const [search, setSearch] = useState("");
  const [searchResults, setSearchResults] = useState([]);
  const [studentDevices, setStudentDevices] = useState([]);
  const [selectedStudent, setSelectedStudent] = useState(null);
  const [loading, setLoading] = useState(false);

  function searchData() {
    if (search.trim() === "") {
      alert("Enter student name, ID or device serial");
      return;
    }
    setLoading(true);

    Promise.all([
      fetch(`http://localhost/elacs/backend/api/students/search.php?q=${search}`).then(res => res.json()),
      fetch(`http://localhost/elacs/backend/api/devices/search.php?q=${search}`).then(res => res.json())
    ]).then(([studentsRes, devicesRes]) => {
      const students = Array.isArray(studentsRes) ? studentsRes : [];
      const devices = Array.isArray(devicesRes) ? devicesRes : [];

      const results = [
        ...students.map(s => ({ ...s, type: 'student' })),
        ...devices.map(d => ({ ...d, type: 'device', student_name: d.student_name || d.full_name || 'Unknown' }))
      ];
      setSearchResults(results);
      setLoading(false);
    }).catch(() => setLoading(false));
  }

  function loadDevices(studentId) {
    setSelectedStudent(studentId);
    fetch(`http://localhost/elacs/backend/api/devices/byStudent.php?student_id=${studentId}`)
      .then(res => res.json())
      .then(data => setStudentDevices(Array.isArray(data) ? data : []));
  }

  function checkinDevice(serial) {
    if (!selectedStudent) {
      alert("Please select a student first");
      return;
    }
    fetch("http://localhost/elacs/backend/api/checkin/create.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ student_id: selectedStudent, device_serial: serial })
    })
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      }
      return res.json();
    })
    .then(data => {
      if (data.error) {
        throw new Error(data.error);
      }
      alert(data.message);
      if (selectedStudent) loadDevices(selectedStudent);
    })
    .catch(err => alert("Check-in failed: " + err.message));
  }

  return (
    <AdminLayout>
      <div className="checkin-container">
        <h2>Check-In Device</h2>
        <div className="search-box">
          <input
            placeholder="Search student, ID or serial..."
            value={search}
            onChange={e => setSearch(e.target.value)}
          />
          <button onClick={searchData} disabled={loading}>
            {loading ? "Searching..." : "Search"}
          </button>
        </div>

        {searchResults.length > 0 && (
          <>
            <h3>Search Results</h3>
            <ul className="search-list">
              {searchResults.map(item => (
                <li key={item.student_id || item.serial_number}>
                  <span>
                    {item.type === 'student' ? `${item.full_name} (${item.student_id}) 👤` : 
                    `${item.model} (${item.serial_number}) 💻 - ${item.student_name}`}
                  </span>
                  {item.type === 'student' ? (
                    <button onClick={() => loadDevices(item.student_id)}>Load Devices</button>
                  ) : (
                    <button onClick={() => loadDevices(item.student_id || prompt('Enter student ID for this device'))}>
                      Check-In
                    </button>
                  )}
                </li>
              ))}
            </ul>
          </>
        )}

        {studentDevices.length > 0 && (
          <>
            <h3>{selectedStudent}'s Devices</h3>
            <table className="device-table">
              <thead>
                <tr>
                  <th>Serial</th>
                  <th>Model</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {studentDevices.map(d => (
                  <tr key={d.serial_number}>
                    <td>{d.serial_number}</td>
                    <td>{d.model}</td>
                    <td className={d.current_status === "IN" ? "status-in" : "status-out"}>
                      {d.current_status || "OUT"}
                    </td>
                    <td>
                      <button
                        className="checkin-btn"
                        disabled={d.current_status === "IN"}
                        onClick={() => checkinDevice(d.serial_number)}
                      >
                        {d.current_status === "IN" ? "Already In" : "Check-In"}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </>
        )}
      </div>
    </AdminLayout>
  );
}

