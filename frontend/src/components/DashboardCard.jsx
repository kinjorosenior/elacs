import React from "react";

function DashboardCard({ title, value }) {
  return (
    <div style={{
      background: "#1e1e2f",
      color: "white",
      padding: "20px",
      borderRadius: "10px",
      width: "200px",
      textAlign: "center",
      boxShadow: "0 4px 10px rgba(0,0,0,0.3)"
    }}>
      <h3>{title}</h3>
      <h1>{value}</h1>
    </div>
  );
}

export default DashboardCard;