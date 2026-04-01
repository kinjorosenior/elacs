import React, { useState } from "react";

function VisitorsTable({ visitors, title }) {
  const [search, setSearch] = useState("");

  const filtered = Array.isArray(visitors)
    ? visitors.filter(v =>
        (v.name || "").toLowerCase().includes(search.toLowerCase()) ||
        (v.phone || "").toLowerCase().includes(search.toLowerCase()) ||
        (v.purpose || "").toLowerCase().includes(search.toLowerCase())
      )
    : [];

  return (
    <div style={{ marginTop: "40px" }}>
      <h2>{title || "Visitors"}</h2>

      <input
        placeholder="Search by name, phone or purpose..."
        value={search}
        onChange={e => setSearch(e.target.value)}
        style={{ padding: "8px", marginBottom: "10px", width: "300px" }}
      />

      <table border="1" cellPadding="10" style={{ width: "100%", borderCollapse: "collapse" }}>
        <thead>
          <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>ID Number</th>
            <th>Purpose</th>
            <th>Sign In Time</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          {filtered.length === 0 ? (
            <tr>
              <td colSpan="6" style={{ textAlign: "center" }}>
                No visitors found
              </td>
            </tr>
          ) : (
            filtered.map((visitor, index) => (
              <tr key={index}>
                <td>{visitor.name || "N/A"}</td>
                <td>{visitor.phone || "N/A"}</td>
                <td>{visitor.id_number || "N/A"}</td>
                <td>{visitor.purpose || "N/A"}</td>
                <td>{visitor.visit_time || visitor.signin_time || "—"}</td>
                <td>
                  <span style={{ 
                    color: visitor.status === 'IN' ? '#10b981' : '#ef4444', 
                    fontWeight: 'bold' 
                  }}>
                    {visitor.status || (visitor.leave_time ? 'OUT' : 'IN')}
                  </span>
                </td>
              </tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  );
}

export default VisitorsTable;
