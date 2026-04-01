import React, { useEffect, useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";

function Profile() {
  const [admin, setAdmin] = useState({
    full_name: "",
    email: "",
    password: "",
    role: "",
    status: "",
  });
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const token = localStorage.getItem("token");

  // Fetch admin profile
  useEffect(() => {
    if (!token) {
      navigate("/login");
      return;
    }

    const fetchProfile = async () => {
      try {
        const res = await axios.get(
          "http://localhost/elacs/backend/api/admins/profile.php",
          { headers: { Authorization: `Bearer ${token}` } }
        );
        if (res.data.error) {
          setError(res.data.error);
        } else {
          setAdmin(res.data);
        }
        setLoading(false);
      } catch (err) {
        console.error(err);
        setError("Failed to fetch profile");
        setLoading(false);
      }
    };

    fetchProfile();
  }, [token, navigate]);

  // Handle input changes
  const handleChange = (e) => {
    setAdmin({ ...admin, [e.target.name]: e.target.value });
  };

  // Update profile
  const updateProfile = async (e) => {
    e.preventDefault();
    setMessage("");
    setError("");

    try {
      const res = await axios.post(
        "http://localhost/elacs/backend/api/admins/update.php",
        admin,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      if (res.data.success) {
        setMessage("Profile updated successfully!");
      } else {
        setError(res.data.error || "Update failed");
      }
    } catch (err) {
      console.error(err);
      setError("Server error while updating profile");
    }
  };

  // Deactivate account
  const deactivateProfile = async () => {
    if (!window.confirm("Are you sure you want to deactivate your account?")) return;

    try {
      const res = await axios.post(
        "http://localhost/elacs/backend/api/admins/deactivate.php",
        { id: admin.id },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      if (res.data.success) {
        alert("Account deactivated. Logging out...");
        localStorage.removeItem("token");
        navigate("/login");
      } else {
        alert(res.data.error || "Failed to deactivate account");
      }
    } catch (err) {
      console.error(err);
      alert("Server error while deactivating account");
    }
  };

  // Logout
  const logout = () => {
    localStorage.removeItem("token");
    navigate("/login");
  };

  if (loading) return <p>Loading profile...</p>;

  return (
    <div style={{ maxWidth: "500px", margin: "40px auto", padding: "20px", border: "1px solid #ccc", borderRadius: "8px" }}>
      <h2>Admin Profile</h2>

      {message && <p style={{ color: "green" }}>{message}</p>}
      {error && <p style={{ color: "red" }}>{error}</p>}

      <form onSubmit={updateProfile} style={{ display: "flex", flexDirection: "column", gap: "15px" }}>
        <input
          type="text"
          name="full_name"
          placeholder="Full Name"
          value={admin.full_name || ""}
          onChange={handleChange}
          required
        />

        <input
          type="email"
          name="email"
          placeholder="Email"
          value={admin.email || ""}
          onChange={handleChange}
          required
        />

        <input
          type="password"
          name="password"
          placeholder="New Password (leave blank to keep current)"
          onChange={handleChange}
        />

        <p><strong>Role:</strong> {admin.role}</p>
        <p><strong>Status:</strong> {admin.status}</p>

        <button type="submit" style={{ padding: "10px", cursor: "pointer", background: "#3b82f6", color: "white", border: "none" }}>Update Profile</button>
      </form>

      <hr style={{ margin: "20px 0" }} />

      <button onClick={deactivateProfile} style={{ padding: "10px", cursor: "pointer", background: "#f87171", color: "white", border: "none", marginBottom: "10px" }}>
        Deactivate Account
      </button>

      <button onClick={logout} style={{ padding: "10px", cursor: "pointer", background: "#94a3b8", color: "white", border: "none" }}>
        Logout
      </button>
    </div>
  );
}

export default Profile;