import React from 'react'
import { BrowserRouter, Routes, Route } from 'react-router-dom'
import SmartUpload from './components/SmartUpload'
import StudentDashboard from './pages/StudentDashboard'

function App() {
  return (
    <BrowserRouter>
      <div className="min-h-screen">
        <Routes>
          <Route path="/" element={<StudentDashboard />} />
          <Route path="/upload" element={<SmartUpload />} />
        </Routes>
      </div>
    </BrowserRouter>
  )
}

export default App
