import React, { useState } from "react";

function DeviceTable({ devices, title }) {
  const [search, setSearch] = useState("");

  const filtered = Array.isArray(devices)
    ? devices.filter(device =>
        (device.serial_number || "").toLowerCase().includes(search.toLowerCase()) ||
        (device.full_name || "").toLowerCase().includes(search.toLowerCase())
      )
    : [];

  return (
    <div style={{ marginTop: "40px" }}>
      <h2>{title || "Devices"}</h2>

      <input
        placeholder="Search by serial or student name..."
        value={search}
        onChange={e => setSearch(e.target.value)}
        style={{ padding: "8px", marginBottom: "10px", width: "300px" }}
      />

      <table border="1" cellPadding="10" style={{ width: "100%", borderCollapse: "collapse" }}>
        <thead>
          <tr>
            <th>Serial Number</th>
            <th>Student</th>
            <th>Type</th>
            <th>Model</th>
            <th>Time</th>
          </tr>
        </thead>

        <tbody>
          {filtered.length === 0 ? (
            <tr>
              <td colSpan="5" style={{ textAlign: "center" }}>
                No devices found
              </td>
            </tr>
          ) : (
            filtered.map((device, index) => (
              <tr key={index}>
                <td>{device.serial_number || "N/A"}</td>
                <td>{device.full_name || device.name || device.student_id || "Unknown"}</td>
                <td>{device.device_type || "N/A"}</td>
                <td>{device.model || "N/A"}</td>
                <td>{device.checkin_time || device.checkout_time || device.created_at || "—"}</td>
              </tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  );
}

export default DeviceTable;
