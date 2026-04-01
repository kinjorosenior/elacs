import React, { useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";

function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError("");

    if (!email || !password) {
      setError("Please enter both email and password");
      return;
    }

    setLoading(true);

    try {
      const res = await axios.post(
        "http://localhost/elacs/backend/api/admins/login.php",
        { email, password }
      );

      const data = res.data;

      if (data.token) {
        localStorage.setItem("token", data.token);
        localStorage.setItem("adminName", data.full_name);
        localStorage.setItem("adminRole", data.role);

        navigate("/"); // Dashboard
      } else if (data.error) {
        setError(data.error);
      } else {
        setError("Login failed. Check credentials.");
      }
    } catch (err) {
      console.error(err);
      setError("Server error. Try again later.");
    }

    setLoading(false);
  };

  return (
    <div style={{
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      justifyContent: "center",
      height: "100vh",
      background: "#f1f5f9"
    }}>
      <div style={{
        maxWidth: "400px",
        width: "100%",
        padding: "30px",
        background: "white",
        borderRadius: "8px",
        boxShadow: "0 0 10px rgba(0,0,0,0.1)"
      }}>
        <h2 style={{ textAlign: "center", marginBottom: "20px" }}>Admin Login</h2>

        {error && <p style={{ color: "red", textAlign: "center" }}>{error}</p>}

        <form onSubmit={handleLogin} style={{ display: "flex", flexDirection: "column", gap: "15px" }}>
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            style={{ padding: "10px", fontSize: "16px" }}
          />

          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            style={{ padding: "10px", fontSize: "16px" }}
          />

          <button type="submit" style={{
            padding: "10px",
            fontSize: "16px",
            background: "#3b82f6",
            color: "white",
            border: "none",
            cursor: "pointer",
            borderRadius: "4px"
          }}>
            {loading ? "Logging in..." : "Login"}
          </button>
        </form>
      </div>
    </div>
  );
}

export default Login;