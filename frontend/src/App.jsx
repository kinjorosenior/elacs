import { BrowserRouter, Routes, Route } from "react-router-dom";

import Dashboard from "./pages/Dashboard";
import Students from "./pages/Students";
import CheckIn from "./pages/CheckIn";
import CheckOut from "./pages/CheckOut";
import Analytics from "./pages/Analytics";
import Reports from "./pages/Reports";
import LibrarySettings from "./pages/LibrarySettings";
import Devices from "./pages/Devices";
import Visitors from "./pages/Visitors";
import Profile from "./pages/Profile";
import Login from "./pages/Login";


import "./App.css";

function App() {
  return (
    <BrowserRouter>
      <div className="main-container">
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/students" element={<Students />} />
          <Route path="/devices" element={<Devices />} />
          <Route path="/visitors" element={<Visitors />} />
          <Route path="/profile" element={<Profile />} />
          <Route path="/checkin" element={<CheckIn />} />
          <Route path="/checkout" element={<CheckOut />} />
          <Route path="/analytics" element={<Analytics />} />

          <Route path="/reports" element={<Reports />} />
          <Route path="/library-settings" element={<LibrarySettings />} />
          <Route path="/login" element={<Login />} />
        </Routes>

      </div>
    </BrowserRouter>
  );
}

export default App;

