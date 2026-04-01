import React, { useState } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import Footer from "../components/Footer";



function AdminLayout({ children }) {
  

  const [collapsed, setCollapsed] = useState(false);

  return (

    <div style={{ display:"flex", minHeight:"100vh", background:"#0f172a", color:"white" }}>

      <Sidebar collapsed={collapsed} />

      <div style={{
        flex:1,
        marginLeft: collapsed ? "70px" : "250px",
        transition:"0.3s"
      }}>

        <div style={{ position: "sticky", top: 0, zIndex: 999, background: "#1e293b" }}>
          <Navbar toggleSidebar={()=>setCollapsed(!collapsed)} />
        </div>

        <main style={{ padding:"25px", marginTop: "20px" }}>
          {children}
        </main>
        

        <Footer />

      </div>

    </div>

  );

}

export default AdminLayout;