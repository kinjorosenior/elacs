import React, { useState, useEffect } from "react";
import AdminLayout from "../layouts/AdminLayout";

export default function LibrarySettings() {
  const [settings, setSettings] = useState({
    open_weekday: "08:00",
    close_weekday: "22:00",
    open_saturday: "08:00",
    close_saturday: "16:00",
    open_sunday: "14:00",
    close_sunday: "17:00",
    reminder_minutes: 30
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetch("http://localhost/elacs/backend/index.php?request=library_settings")
      .then(res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then(data => {
        console.log("API response:", data);
        if (data && typeof data === 'object' && Object.keys(data).length > 0) {
          setSettings({
            open_weekday: data.open_weekday || "08:00",
            close_weekday: data.close_weekday || "22:00",
            open_saturday: data.open_saturday || "08:00",
            close_saturday: data.close_saturday || "16:00",
            open_sunday: data.open_sunday || "14:00",
            close_sunday: data.close_sunday || "17:00",
            reminder_minutes: parseInt(data.reminder_minutes) || 30
          });
        }
        setLoading(false);
      })
      .catch(err => {
        console.error("Settings load error:", err);
        setError("Failed to load (warnings OK, table empty?). Using defaults.");
        setLoading(false);
      });
  }, []);

  const handleChange = (e) => {
    setSettings({...settings, [e.target.name]: e.target.value });
    setError(null);
  };

  const saveSettings = () => {
    setSaving(true);
    setError(null);
    fetch("http://localhost/elacs/backend/index.php?request=update_library", {
      method: "POST",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify(settings)
    }).then(res => {
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return res.json();
    }).then(data => {
      alert("Settings saved!");
      setSaving(false);
    }).catch(err => {
      console.error("Save error:", err);
      setError("Save failed. Check console/backend.");
      setSaving(false);
    });
  };

  if (loading) return (
    <AdminLayout>
      <div style={{padding: "40px", textAlign: "center", color: "white"}}>
        Loading settings...
      </div>
    </AdminLayout>
  );

  return (
    <AdminLayout>
      <h1 style={{color: "white"}}>Library Settings</h1>
      {error && (
        <div style={{color: "orange", marginBottom: "20px", padding: "10px", background: "#1e293b", borderRadius: "4px"}}>
          {error}
        </div>
      )}
      <div style={{maxWidth: "500px", padding: "20px", background: "#1e293b", borderRadius: "8px"}}>
        <h3 style={{color: "white"}}>Weekday (Mon-Fri)</h3>
        <div style={{display: "flex", gap: "10px", marginBottom: "20px"}}>
          <input type="time" name="open_weekday" value={settings.open_weekday} onChange={handleChange} style={{flex: 1}} />
          <span style={{color: "white", alignSelf: "center"}}>to</span>
          <input type="time" name="close_weekday" value={settings.close_weekday} onChange={handleChange} style={{flex: 1}} />
        </div>

        <h3 style={{color: "white"}}>Saturday</h3>
        <div style={{display: "flex", gap: "10px", marginBottom: "20px"}}>
          <input type="time" name="open_saturday" value={settings.open_saturday} onChange={handleChange} style={{flex: 1}} />
          <span style={{color: "white", alignSelf: "center"}}>to</span>
          <input type="time" name="close_saturday" value={settings.close_saturday} onChange={handleChange} style={{flex: 1}} />
        </div>

        <h3 style={{color: "white"}}>Sunday</h3>
        <div style={{display: "flex", gap: "10px", marginBottom: "20px"}}>
          <input type="time" name="open_sunday" value={settings.open_sunday} onChange={handleChange} style={{flex: 1}} />
          <span style={{color: "white", alignSelf: "center"}}>to</span>
          <input type="time" name="close_sunday" value={settings.close_sunday} onChange={handleChange} style={{flex: 1}} />
        </div>

        <h3 style={{color: "white"}}>Reminder</h3>
        <div style={{marginBottom: "20px"}}>
          <input type="number" name="reminder_minutes" value={settings.reminder_minutes} onChange={handleChange} min="5" max="120" style={{width: "100px"}} />
          <span style={{color: "white", marginLeft: "10px"}}>minutes before closing</span>
        </div>

        <button onClick={saveSettings} disabled={saving} style={{padding: "12px 24px", background: "#10b981", color: "white", border: "none", borderRadius: "4px", cursor: "pointer", fontSize: "16px"}}>
          {saving ? "Saving..." : "💾 Save Settings"}
        </button>
      </div>
    </AdminLayout>
  );
}

