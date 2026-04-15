import React from "react";
import { Link } from "react-router-dom";

function Navbar({ toggleSidebar = () => {} }) {

  return (

    <div style={{
      height:"60px",
      background:"#1e293b",
      display:"flex",
      alignItems:"center",
      justifyContent:"space-between",
      padding:"0 20px"
    }}>

      <button
        onClick={toggleSidebar}
        style={{
          background:"none",
          border:"none",
          color:"white",
          fontSize:"20px",
          cursor:"pointer"
        }}
      >
        ☰
      </button>

      <h3>ELECTRONIC GADGETS LIBRARY CHECKIN SYSTEM</h3>

      <div style={{display:"flex",gap:"20px"}}>

        <Link to="/" style={{color:"white"}}>Dashboard</Link>

        <Link to="/students" style={{color:"white"}}>Students</Link>

        <Link to="/devices" style={{color:"white"}}>Devices</Link>


        <Link to="/profile" style={{color:"white"}}>Profile</Link>
        <Link to="/library-settings" style={{color:"white"}}>Settings</Link>

      </div>


      <button
        onClick={()=>{

          localStorage.removeItem("token");
          window.location="/";

        }}
        style={{
          padding:"6px 12px",
          background:"#ef4444",
          border:"none",
          color:"white"
        }}
      >
        Logout
      </button>

    </div>

  );

}

export default Navbar;