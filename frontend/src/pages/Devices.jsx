import { useState, useEffect } from "react";
import AdminLayout from "../layouts/AdminLayout";

export default function Devices() {
  const [showModal, setShowModal] = useState(false);
  const [editModal, setEditModal] = useState(false);
  const [devices, setDevices] = useState([]);
  const [search, setSearch] = useState("");
  const [searchResults, setSearchResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [device, setDevice] = useState({
    id: "",
    student_id: "",
    serial_number: "",
    model: "",
    device_type: "",
    color: "",
    marks: ""
  });

  function handleChange(e) {
    setDevice({ ...device, [e.target.name]: e.target.value });
  }

  function fetchDevices() {
    fetch("http://localhost/elacs/backend/api/devices/read.php")
      .then(res => res.json())
      .then(data => setDevices(Array.isArray(data) ? data : []))
      .catch(() => setDevices([]));
  }

  function searchDevices() {
    if (!search.trim()) return setSearchResults([]);
    setLoading(true);
    Promise.all([
      fetch(`http://localhost/elacs/backend/api/students/search.php?q=${search}`).then(r => r.json()),
      fetch(`http://localhost/elacs/backend/api/devices/search.php?q=${search}`).then(r => r.json())
    ]).then(([students, deviceRes]) => {
      const results = [
        ...students.map(s => ({ ...s, type: 'student' })),
        ...deviceRes.map(d => ({ ...d, type: 'device', student_name: d.student_name || 'Unknown' }))
      ];
      setSearchResults(results);
      setLoading(false);
    }).catch(() => setLoading(false));
  }

  async function checkinDevice(serial, studentId = null) {
    const sid = studentId || prompt("Enter Student ID for check-in");
    if (!sid) return;
    try {
      const res = await fetch("http://localhost/elacs/backend/api/checkin/create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ student_id: sid, serial_number: serial })
      });
      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      }
      const data = await res.json();
      if (data.error) {
        throw new Error(data.error);
      }
      alert(data.message || "Check-in success");
      fetchDevices();
    } catch (err) {
      alert("Check-in failed: " + err.message);
    }
  }

  function openEdit(deviceData) {
    setDevice(deviceData);
    setEditModal(true);
  }

  function submitDevice(e) {
    e.preventDefault();
    const isEdit = !!device.id;
    const url = isEdit ? "http://localhost/elacs/backend/api/devices/update.php" : "http://localhost/elacs/backend/api/devices/create.php";
    const body = isEdit ? { id: device.id, ...device } : { ...device, brand: device.serial_number.substring(0, 2).toUpperCase() };

    if (!isEdit) {
      const serialPrefix = device.serial_number.substring(0, 2).toUpperCase();
      if (!['HP', 'DELL', 'LENOVO', 'ASUS', 'ACER'].includes(serialPrefix)) {
        alert('Serial must start with HP, DELL, LENOVO, ASUS, or ACER');
        return;
      }
    }

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    }).then(res => res.json()).then(() => {
      alert(isEdit ? "Device updated" : "Laptop registered");
      setShowModal(false);
      setEditModal(false);
      setDevice({ id: "", student_id: "", serial_number: "", model: "", device_type: "", color: "", marks: "" });
      fetchDevices();
    }).catch(() => alert("Failed"));
  }

  function toggleDeviceStatus(id, currentStatus) {
    const action = currentStatus === 'active' || !currentStatus ? 'Deactivate' : 'Activate';
    if (!confirm(`${action} this device?`)) return;
    fetch(`http://localhost/elacs/backend/api/devices/${action.toLowerCase()}.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    }).then(() => {
      alert(`${action}d`);
      fetchDevices();
    });
  }

  useEffect(() => {
    fetchDevices();
  }, []);

  const tableData = search ? searchResults : devices;

  return (
    <AdminLayout>
      <div className="devices-container">
        <div style={{ display: "flex", gap: "1rem", alignItems: "center", marginBottom: "2rem" }}>
          <h2>Laptops / Devices</h2>
          <input
            placeholder="Search student or serial..."
            value={search}
            onChange={e => setSearch(e.target.value)}
            className="search-input"
          />
          <button onClick={searchDevices} disabled={loading} className="btn">
            {loading ? "Searching..." : "Search"}
          </button>
          <button onClick={() => setShowModal(true)} className="btn success">
            + Register Laptop
          </button>
        </div>

        <table className="admin-table">
          <thead>
            <tr>
              <th>Serial</th>
              <th>Student</th>
              <th>Model</th>
              <th>Type</th>
              <th>Color</th>
              <th>Status</th>
              <th>Check-in</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {tableData.map(d => (
              <tr key={d.id || d.serial_number}>
                <td>{d.serial_number || d.student_id}</td>
                <td>{d.student_name || d.student_id || d.full_name}</td>
                <td>{d.model}</td>
                <td>{d.device_type}</td>
                <td>{d.color}</td>
                <td>
                  <span className={`status ${d.current_status || d.status || 'active'}`}>
                    {d.current_status || d.status || 'Active'}
                  </span>
                </td>
                <td>
                  <button
                    onClick={() => checkinDevice(d.serial_number, d.student_id)}
                    className="btn success"
                  >
                    Check-in
                  </button>
                </td>
                <td>
                  <button onClick={() => openEdit(d)} className="btn">
                    Edit
                  </button>
                  <button onClick={() => toggleDeviceStatus(d.id, d.current_status || d.status)} className="btn danger">
                    {(d.current_status || d.status) === 'active' || !d.current_status && !d.status ? 'Deactivate' : 'Activate'}
                  </button>
                </td>
              </tr>
            ))}
            {tableData.length === 0 && (
              <tr>
                <td colSpan="8">No devices found</td>
              </tr>
            )}
          </tbody>
        </table>

        {showModal && (
          <div className="modal-overlay">
            <div className="modal">
              <h3>{device.id ? "Edit Device" : "Register New Laptop"}</h3>
              <form onSubmit={submitDevice}>
                <input name="student_id" placeholder="Student ID *" value={device.student_id} onChange={handleChange} required />
                <input name="serial_number" placeholder="Serial Number *" value={device.serial_number} onChange={handleChange} required />
                <input name="model" placeholder="Model" value={device.model} onChange={handleChange} />
                <select name="device_type" value={device.device_type} onChange={handleChange}>
                  <option value="">Select Type</option>
                  <option>Laptop</option>
                  <option>Tablet</option>
                </select>
                <input name="color" placeholder="Color" value={device.color} onChange={handleChange} />
                <input name="marks" placeholder="Marks" value={device.marks} onChange={handleChange} />
                <button type="submit" className="btn success">{device.id ? "Update" : "Register"}</button>
              </form>
              <button onClick={() => setShowModal(false)} className="btn">Close</button>
            </div>
          </div>
        )}

        {editModal && (
          <div className="modal-overlay">
            <div className="modal">
              <h3>Edit Device</h3>
              <form onSubmit={submitDevice}>
                <input name="student_id" placeholder="Student ID *" value={device.student_id} onChange={handleChange} required />
                <input name="serial_number" placeholder="Serial Number *" value={device.serial_number} onChange={handleChange} required />
                <input name="model" placeholder="Model" value={device.model} onChange={handleChange} />
                <select name="device_type" value={device.device_type} onChange={handleChange}>
                  <option value="">Select Type</option>
                  <option>Laptop</option>
                  <option>Tablet</option>
                </select>
                <input name="color" placeholder="Color" value={device.color} onChange={handleChange} />
                <input name="marks" placeholder="Marks" value={device.marks} onChange={handleChange} />
                <button type="submit" className="btn success">Update Device</button>
              </form>
              <button onClick={() => { setEditModal(false); setDevice({}); }} className="btn">Close</button>
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  );
}

