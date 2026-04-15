import { useState } from "react";
import AdminLayout from "../layouts/AdminLayout";
import "../styles/checkin.css";

export default function CheckOut() {
  const [serial, setSerial] = useState("");
  const [device, setDevice] = useState(null);

  function searchDevice() {
    fetch(`http://localhost/elacs/backend/api/devices/search.php?q=${serial}`)
      .then(res => res.json())
      .then(data => {
        if (data.length > 0) {
          setDevice(data[0]);
        } else {
          alert("Device not found");
          setDevice(null);
        }
      });
  }

  function checkout() {
    fetch("http://localhost/elacs/backend/api/checkout/update.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ serial_number: serial })
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        setDevice(null);
        setSerial("");
      });
  }

  return (
    <AdminLayout>
      <div className="checkout-container">
        <h2>Check-Out Device</h2>
        <div className="search-box">
          <input
            placeholder="Enter Device Serial"
            value={serial}
            onChange={e => setSerial(e.target.value)}
          />
          <button onClick={searchDevice}>Search Device</button>
        </div>

        {device && (
          <div className="device-info">
            <p><strong>Model:</strong> {device.model}</p>
            <p><strong>Serial:</strong> {device.serial_number}</p>
            <p><strong>Student:</strong> {device.student_name || device.full_name || 'N/A'}</p>
            <p><strong>Status:</strong> {device.current_status || 'OUT'}</p>
          </div>
        )}

        {device && (
          <button className="checkout-btn" onClick={checkout}>
            Check-Out Device
          </button>
        )}
      </div>
    </AdminLayout>
  );
}
