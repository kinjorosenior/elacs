import React from "react";

function Modal({title, children, closeModal}) {

  return (

    <div style={{
      position:"fixed",
      top:0,
      left:0,
      width:"100%",
      height:"100%",
      background:"rgba(0,0,0,0.6)",
      display:"flex",
      justifyContent:"center",
      alignItems:"center",
      zIndex:1000
    }}>

      <div style={{
        background:"#1e293b",
        padding:"25px",
        borderRadius:"8px",
        width:"400px",
        color:"white"
      }}>

        <div style={{
          display:"flex",
          justifyContent:"space-between",
          marginBottom:"20px"
        }}>

          <h3>{title}</h3>

          <button onClick={closeModal} style={{
            background:"none",
            border:"none",
            color:"white",
            fontSize:"18px",
            cursor:"pointer"
          }}>
            ✖
          </button>

        </div>

        {children}

      </div>

    </div>

  );

}

export default Modal;