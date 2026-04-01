import React, { useState, useEffect } from "react";
import AdminLayout from "../layouts/AdminLayout";

const loadVisitors = (setVisitors, setLoading) => {
  fetch("http://localhost/elacs/backend/api/visitors/read.php")
    .then(res => res.json())
    .then(data => setVisitors(Array.isArray(data.visitors) ? data.visitors : data))
    .finally(() => setLoading(false));
};

const signOutVisitor = (id, setVisitors) => {
  fetch("http://localhost/elacs/backend/api/visitors/checkout.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({id})
  }).then(res => res.json()).then(data => {
    alert(data.message || data.error);
    loadVisitors(setVisitors, false);
  });
};

export default function Visitors() {
  const [visitors, setVisitors] = useState([]);
  const [showModal, setShowModal] = useState(false);
  const [formData, setFormData] = useState({
    name: "",
    phone: "",
    id_number: "",
    purpose: ""
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadVisitors(setVisitors, setLoading);
  }, []);

  const handleInput = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const addVisitor = (e) => {
    e.preventDefault();
    fetch("http://localhost/elacs/backend/api/visitors/create.php", {
      method: "POST",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify(formData)
    }).then(res => res.json()).then(() => {
      alert("Visitor signed in");
      setShowModal(false);
      setFormData({name:"", phone:"", id_number:"", purpose:""});
      loadVisitors(setVisitors, setLoading);
    });
  };

  if (loading) return <AdminLayout><div style={{padding: "40px", textAlign: "center"}}>Loading...</div></AdminLayout>;

  return (
    <AdminLayout>
      <div style={{display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: "20px"}}>
        <h2>Library Visitors</h2>
        <button onClick={() => setShowModal(true)} style={{padding: "10px 20px", background: "#3b82f6", color: "white", border: "none"}}>
          + Sign In Visitor
        </button>
      </div>
      
      <table style={{width: "100%", borderCollapse: "collapse"}}>
        <thead>
          <tr style={{background: "#1e293b"}}>
            <th style={{padding: "12px", border: "1px solid #475569"}}>Name</th>
            <th style={{padding: "12px", border: "1px solid #475569"}}>Phone</th>
            <th style={{padding: "12px", border: "1px solid #475569"}}>ID Number</th>
            <th style={{padding: "12px", border: "1px solid #475569"}}>Purpose</th>
            <th style={{padding: "12px", border: "1px solid #475569"}}>Sign In</th>
            <th style={{padding: "12px", border: "1px solid #475569"}}>Sign Out</th>
            <th style={{padding: "12px", border: "1px solid #475569"}}>Status</th>
          </tr>
        </thead>
        <tbody>
          {visitors.map(v => (
            <tr key={v.id}>
              <td style={{padding: "12px", border: "1px solid #475569"}}>{v.name}</td>
              <td style={{padding: "12px", border: "1px solid #475569"}}>{v.phone}</td>
              <td style={{padding: "12px", border: "1px solid #475569"}}>{v.id_number}</td>
              <td style={{padding: "12px", border: "1px solid #475569"}}>{v.purpose}</td>
              <td style={{padding: "12px", border: "1px solid #475569"}}>{v.signin_time}</td>
              <td style={{padding: "12px", border: "1px solid #475569"}}>{v.leave_time || '—'}</td>
              <td style={{padding: "12px", border: "1px solid #475569"}}>
                <span style={{color: v.status === 'IN' ? '#10b981' : '#ef4444', fontWeight: 'bold'}}>
                  {v.status || (v.leave_time ? 'OUT' : 'IN')}
                </span>
                {v.status === 'IN' && (
                  <button onClick={() => signOutVisitor(v.id, setVisitors)} style={{marginLeft: "10px", padding: "4px 8px", background: "#ef4444", color: "white", border: "none"}}>
                    Sign Out
                  </button>
                )}
              </td>
            </tr>
          ))}
          {visitors.length === 0 && (
            <tr>
              <td colSpan="7" style={{padding: "40px", textAlign: "center", color: "#94a3b8"}}>
                No visitors today
              </td>
            </tr>
          )}
        </tbody>
      </table>

      {showModal && (
        <div style={{position: "fixed", inset: 0, background: "rgba(0,0,0,0.5)", display: "flex", zIndex: 1000}} onClick={() => setShowModal(false)}>
          <div style={{margin: "auto", background: "white", color: "black", padding: "30px", borderRadius: "10px", width: "400px"}} onClick={e => e.stopPropagation()}>
            <h3>Sign In New Visitor</h3>
            <form onSubmit={addVisitor}>
              <input name="name" placeholder="Full Name *" required onChange={handleInput} value={formData.name} style={{width: "100%", padding: "10px", margin: "10px 0", borderRadius: "5px", border: "1px solid #ccc"}} />
              <input name="phone" placeholder="Phone *" required onChange={handleInput} value={formData.phone} style={{width: "100%", padding: "10px", margin: "10px 0", borderRadius: "5px", border: "1px solid #ccc"}} />
              <input name="id_number" placeholder="ID Number *" required onChange={handleInput} value={formData.id_number} style={{width: "100%", padding: "10px", margin: "10px 0", borderRadius: "5px", border: "1px solid #ccc"}} />
              <input name="purpose" placeholder="Purpose of Visit *" required onChange={handleInput} value={formData.purpose} style={{width: "100%", padding: "10px", margin: "10px 0", borderRadius: "5px", border: "1px solid #ccc"}} />
              <div style={{display: "flex", gap: "10px"}}>
                <button type="submit" style={{flex: 1, padding: "12px", background: "#10b981", color: "white", border: "none", borderRadius: "5px", cursor: "pointer"}}>Sign In</button>
                <button type="button" onClick={() => setShowModal(false)} style={{flex: 1, padding: "12px", background: "#6b7280", color: "white", border: "none", borderRadius: "5px", cursor: "pointer"}}>Cancel</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}

