import React from "react";
import { Link } from "react-router-dom";

function Sidebar({ collapsed }) {

  const width = collapsed ? "70px" : "250px";

  const item = {
    display: "block",
    padding: "10px",
    marginTop: "10px",
    textDecoration: "none",
    color: "white"
  };

  return (

  <div style={{
  width: width,
  height: "100vh",
  background: "#020617",
  padding: "20px",
  position: "fixed",
  left: 0,
  top: 0,
  transition: "0.3s",
  zIndex: 1000
}}>

  {!collapsed && <h2>CHECKIN SYSTEM</h2>}

<Link to="/" style={item}>
  {collapsed ? "🏠" : "🏠 Dashboard"}
</Link>

      {!collapsed && <Link to="/" style={item}>Dashboard</Link>}
      {!collapsed && <Link to="/students" style={item}>Students</Link>}
      {!collapsed && <Link to="/devices" style={item}>Laptops</Link>}
      {!collapsed && <Link to="/checkin" style={item}>Check-In</Link>}
      {!collapsed && <Link to="/checkout" style={item}>Check-Out</Link>}

      {!collapsed && <Link to="/analytics" style={item}>Analytics</Link>}

      {!collapsed && <Link to="/reports" style={item}>Reports</Link>}
      {!collapsed && <Link to="/library-settings" style={item}>⚙️ Settings</Link>}

      {!collapsed && <Link to="/visitors" style={item}>👥 Visitors</Link>}


    </div>

  );

}

export default Sidebar;