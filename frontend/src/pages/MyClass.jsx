import React, { useState, useEffect } from 'react';
import { Users, FileText, CheckCircle, Clock, Printer } from 'lucide-react';
import { motion } from 'framer-motion';
import { Link, useNavigate } from 'react-router-dom';
import api from '../api';
import Modal from '../components/ui/Modal';

const MyClass = () => {
  const navigate = useNavigate();
  const [classData, setClassData] = useState(null);
  const [students, setStudents] = useState([]);
  const [session, setSession] = useState(null);
  const [term, setTerm] = useState(null);
  const [loading, setLoading] = useState(true);
  const [filterPending, setFilterPending] = useState(false);

  // Print Modal State
  const [printModalOpen, setPrintModalOpen] = useState(false);
  const [printTarget, setPrintTarget] = useState(null);
  const [printTotalCount, setPrintTotalCount] = useState('');

  useEffect(() => {
    const fetchClassData = async () => {
      try {
        const response = await api.get('/my-class');
        setClassData(response.data.class);
        setStudents(response.data.students || []);
        setSession(response.data.currentSession);
        setTerm(response.data.currentTerm);
      } catch (err) {
        console.error('Failed to fetch class', err);
      } finally {
        setLoading(false);
      }
    };
    fetchClassData();
  }, []);

  if (loading) return <div className="p-8 text-center text-slate-500">Loading your class data...</div>;

  if (!classData) return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] text-center">
      <div className="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
        <Users className="w-8 h-8" />
      </div>
      <h2 className="text-xl font-bold text-slate-800">No Class Assigned</h2>
      <p className="text-slate-500 mt-2 max-w-md">You are currently not assigned as a class teacher to any class. Please contact the Head Teacher.</p>
    </div>
  );

  const displayedStudents = filterPending ? students.filter(s => s.is_pending) : students;
  const pendingCount = students.filter(s => s.is_pending).length;

  const handleDeleteResult = async (studentId) => {
    if (!await window.confirmAction("Are you sure you want to delete this student's result? All entered scores will be permanently deleted.")) return;
    try {
      await api.delete(`/my-class/students/${studentId}/results`);
      alert("Result deleted successfully");
      window.location.reload();
    } catch (err) {
      alert("Failed to delete result");
    }
  };

  const openPrintModal = (target) => {
    setPrintTarget(target);
    setPrintTotalCount('');
    setPrintModalOpen(true);
  };

  const executePrint = (e) => {
    e.preventDefault();
    const baseUrl = api.defaults.baseURL.replace('/api', '');
    let url = printTarget === 'all' 
      ? `${baseUrl}/results/print-all`
      : `${baseUrl}/results/print/${printTarget}`;
      
    if (printTotalCount) {
      url += `?ts=${encodeURIComponent(printTotalCount)}`;
    }
    window.open(url, '_blank');
    setPrintModalOpen(false);
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">My Class: {classData.name}</h1>
          <p className="text-sm text-slate-500 mt-1">Manage results for your students.</p>
        </div>
        
        <div className="flex items-center gap-3">
          {session && term && (
            <span className="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-royal-blue/10 text-royal-blue">
              {session.name} • {term.name}
            </span>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="glass-card p-6 flex flex-col justify-center bg-gradient-to-br from-royal-blue to-blue-700 text-white relative overflow-hidden">
          <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -translate-y-10 translate-x-10"></div>
          <h3 className="text-xl font-bold mb-2 relative z-10">Batch Print Operations</h3>
          <p className="text-blue-100 text-sm mb-4 relative z-10">Generate termly report cards for the entire class at once.</p>
          <button 
            onClick={() => openPrintModal('all')}
            className="self-start inline-flex items-center gap-2 px-5 py-2.5 bg-white text-royal-blue font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-lg relative z-10"
          >
            <Printer className="w-5 h-5" /> Download All Results
          </button>
        </div>

        <div className="glass-card p-6 flex flex-col justify-center space-y-4">
          <div className="flex justify-between items-center">
            <h3 className="font-bold text-slate-800">Class Progress</h3>
            <span className="text-xs font-medium bg-slate-100 px-2 py-1 rounded-md text-slate-500">
              {students.length - pendingCount} / {students.length} Completed
            </span>
          </div>
          
          <div className="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
            <div 
              className="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" 
              style={{ width: `${students.length > 0 ? ((students.length - pendingCount) / students.length) * 100 : 0}%` }}
            ></div>
          </div>
          
          <div className="flex gap-4">
            <button 
              onClick={() => setFilterPending(false)}
              className={`flex-1 p-3 rounded-xl border transition-all text-sm font-medium ${!filterPending ? 'bg-royal-blue/5 border-royal-blue/20 text-royal-blue' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100'}`}
            >
              All Students
            </button>
            <button 
              onClick={() => setFilterPending(true)}
              className={`flex-1 p-3 rounded-xl border transition-all text-sm font-medium flex items-center justify-center gap-2 ${filterPending ? 'bg-orange-50 border-orange-200 text-orange-600' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100'}`}
            >
              Pending {pendingCount > 0 && <span className="bg-orange-500 text-white px-2 py-0.5 rounded-full text-xs">{pendingCount}</span>}
            </button>
          </div>
        </div>
      </div>

      <div className="glass-card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600">
            <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="px-6 py-4">Reg No.</th>
                <th className="px-6 py-4">Name</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {displayedStudents.length === 0 ? (
                <tr>
                  <td colSpan="4" className="px-6 py-8 text-center text-slate-400">No students found.</td>
                </tr>
              ) : (
                displayedStudents.map((student, idx) => (
                  <motion.tr 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: idx * 0.02 }}
                    key={student.id} 
                    className="hover:bg-slate-50/50 transition-colors"
                  >
                    <td className="px-6 py-4 font-mono text-xs text-slate-500">{student.registration_number || 'N/A'}</td>
                    <td className="px-6 py-4 font-medium text-slate-800">{student.first_name} {student.surname}</td>
                    <td className="px-6 py-4">
                      {student.is_pending ? (
                        <span className="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-orange-50 text-orange-600 border border-orange-100">
                          <Clock className="w-3 h-3" /> Pending
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-green-50 text-green-600 border border-green-100">
                          <CheckCircle className="w-3 h-3" /> Complete
                        </span>
                      )}
                    </td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex justify-end gap-2">
                        <Link 
                          to={`/my-class/students/${student.id}/results`}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-royal-blue text-white text-xs font-medium rounded-lg hover:bg-blue-800 transition-colors"
                        >
                          <FileText className="w-3 h-3" />
                          Edit
                        </Link>
                        <button
                          onClick={() => openPrintModal(student.id)}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-200 transition-colors"
                        >
                          Print
                        </button>
                        <button
                          onClick={() => handleDeleteResult(student.id)}
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </motion.tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
      
      {/* Print Modal */}
      <Modal
        isOpen={printModalOpen}
        onClose={() => setPrintModalOpen(false)}
        title={printTarget === 'all' ? "Print Class Results" : "Print Student Result"}
      >
        <form onSubmit={executePrint} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">
              Total Students in Class (Optional)
            </label>
            <p className="text-xs text-slate-500 mb-2">
              Leave blank to automatically count active students. Enter a number to override the "Total Students" shown on the report card.
            </p>
            <input 
              type="number"
              min="1"
              className="w-full glass-input"
              placeholder="e.g. 45"
              value={printTotalCount}
              onChange={(e) => setPrintTotalCount(e.target.value)}
            />
          </div>
          <div className="pt-4 flex justify-end gap-3">
            <button 
              type="button" 
              onClick={() => setPrintModalOpen(false)}
              className="px-4 py-2 text-slate-500 hover:bg-slate-100 rounded-xl transition-colors font-medium"
            >
              Cancel
            </button>
            <button 
              type="submit"
              className="px-4 py-2 bg-royal-blue text-white rounded-xl hover:bg-blue-800 transition-colors font-medium flex items-center gap-2"
            >
              <Printer className="w-4 h-4" /> Print Now
            </button>
          </div>
        </form>
      </Modal>

    </div>
  );
};

export default MyClass;
