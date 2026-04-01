import React from "react";

function Footer() {
  return (
    <div style={{
      height: "40px",
      background: "#1e293b",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      borderTop: "1px solid #334155",
      fontSize: "13px"
    }}>
      ELACS © {new Date().getFullYear()}
    </div>
  );
}

export default Footer;