import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Teachers from './pages/Teachers';
import Classes from './pages/Classes';
import Subjects from './pages/Subjects';
import Students from './pages/Students';
import Sessions from './pages/Sessions';
import Grading from './pages/Grading';
import MyClass from './pages/MyClass';
import MyClassSubjects from './pages/MyClassSubjects';
import StudentResults from './pages/StudentResults';
import FeeSetup from './pages/finance/FeeSetup';
import StudentBilling from './pages/finance/StudentBilling';
import StudentFinance from './pages/finance/StudentFinance';
import PaymentApprovals from './pages/finance/PaymentApprovals';
import Apply from './pages/Apply';
import Admissions from './pages/Admissions';
import ChangePassword from './pages/ChangePassword';
import StudentLogin from './pages/StudentLogin';
import StudentPortal from './pages/StudentPortal';
import GlassLayout from './components/layout/GlassLayout';

import Landing from './pages/Landing';

function App() {
  return (
    <>
      <Toaster position="top-right" toastOptions={{ 
        duration: 4000,
        style: {
          background: '#fff',
          color: '#334155',
          boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
          borderRadius: '0.75rem',
          border: '1px solid #f1f5f9',
          fontWeight: '500'
        },
        success: {
          iconTheme: { primary: '#10b981', secondary: '#fff' },
        },
        error: {
          iconTheme: { primary: '#ef4444', secondary: '#fff' },
        }
      }} />
      <Router>
      <Routes>
        <Route path="/" element={<Landing />} />
        <Route path="/login" element={<Login />} />
        
        {/* Protected Dashboard Routes */}
        <Route element={<GlassLayout />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="teachers" element={<Teachers />} />
          <Route path="classes" element={<Classes />} />
          <Route path="classes/:id/subjects" element={<Subjects />} />
          <Route path="students" element={<Students />} />
          <Route path="sessions" element={<Sessions />} />
          <Route path="grading" element={<Grading />} />
          {/* Class Teacher Routes */}
          <Route path="my-class" element={<MyClass />} />
          <Route path="my-class/subjects" element={<MyClassSubjects />} />
          <Route path="my-class/students/:id/results" element={<StudentResults />} />
          {/* Finance Routes */}
          <Route path="finance/setup" element={<FeeSetup />} />
          <Route path="finance/bills" element={<StudentBilling />} />
          <Route path="finance/student/:id" element={<StudentFinance />} />
          <Route path="finance/approvals" element={<PaymentApprovals />} />
          <Route path="admissions" element={<Admissions />} />
          <Route path="settings/password" element={<ChangePassword />} />
        </Route>
        
        {/* Public Routes */}
        <Route path="/apply" element={<Apply />} />
        <Route path="/student-login" element={<StudentLogin />} />
        <Route path="/student-portal" element={<StudentPortal />} />
      </Routes>
    </Router>
    </>
  );
}

export default App;
