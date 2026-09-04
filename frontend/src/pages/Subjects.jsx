import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { BookOpen, Plus, Trash2, ArrowLeft, Calendar } from 'lucide-react';
import { motion } from 'framer-motion';
import api from '../api';

const Subjects = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [subjects, setSubjects] = useState([]);
  const [classInfo, setClassInfo] = useState(null);
  const [session, setSession] = useState(null);
  const [term, setTerm] = useState(null);
  const [loading, setLoading] = useState(true);
  const [newSubject, setNewSubject] = useState('');

  const fetchSubjects = async () => {
    try {
      const response = await api.get(`/classes/${id}/subjects`);
      setSubjects(response.data.subjects || []);
      setClassInfo(response.data.class);
      setSession(response.data.session);
      setTerm(response.data.term);
    } catch (err) {
      console.error('Failed to fetch subjects', err);
      if (err.response?.status === 404) navigate('/classes');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSubjects();
  }, [id]);

  const handleAddSubject = async (e) => {
    e.preventDefault();
    if (!newSubject.trim()) return;

    try {
      await api.post(`/classes/${id}/subjects`, { name: newSubject });
      setNewSubject('');
      fetchSubjects();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to add subject');
    }
  };

  const handleDelete = async (subjectId) => {
    if (await window.confirmAction('Remove this subject? This might affect existing results.')) {
      try {
        await api.delete(`/classes/${id}/subjects/${subjectId}`);
        fetchSubjects();
      } catch (err) {
        alert(err.response?.data?.error || 'Failed to delete');
      }
    }
  };

  if (loading) return <div className="p-8 text-center text-slate-500">Loading subjects...</div>;

  return (
    <div className="space-y-6 max-w-4xl mx-auto">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-4">
          <button 
            onClick={() => {
              api.get('/me').then(res => {
                if (res.data.user.role === 'Class Teacher') {
                  navigate('/my-class');
                } else {
                  navigate('/classes');
                }
              }).catch(() => navigate(-1));
            }}
            className="p-2 bg-white/60 border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors text-slate-600"
          >
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Manage Subjects</h1>
            <p className="text-sm text-slate-500 mt-1">Class: <span className="font-semibold text-royal-blue">{classInfo?.name}</span></p>
          </div>
        </div>
        
        {/* Session Badge */}
        {(session || term) && (
          <div className="flex items-center gap-2 bg-white/60 border border-slate-200 px-4 py-2 rounded-full shadow-sm">
            <Calendar className="w-4 h-4 text-royal-blue" />
            <span className="text-xs font-semibold text-slate-700">{session?.name} • {term?.name}</span>
          </div>
        )}
      </div>

      <div className="glass-card overflow-hidden">
        <div className="p-6 border-b border-slate-200 bg-white/50 space-y-3">
          <div className="flex items-center justify-between">
            <h2 className="text-sm font-bold text-slate-700">Add New Subject</h2>
          </div>
          <form onSubmit={handleAddSubject} className="flex flex-col sm:flex-row gap-3">
            <input
              type="text"
              placeholder="e.g. Mathematics, English Language..."
              value={newSubject}
              onChange={(e) => setNewSubject(e.target.value)}
              className="flex-1 glass-input focus:ring-royal-blue/20"
            />
            <button type="submit" className="glass-button flex items-center justify-center gap-2 whitespace-nowrap bg-emerald-600 hover:bg-emerald-700">
              <Plus className="w-4 h-4" /> Add Subject
            </button>
          </form>
          <p className="text-xs text-slate-500 mt-2">Subjects added here will instantly appear on the result entry page for the active term.</p>
        </div>

        <div className="p-0 overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600 min-w-[300px]">
            <tbody className="divide-y divide-slate-100">
              {subjects.length === 0 ? (
                <tr>
                  <td className="px-6 py-12 text-center text-slate-400">
                    <BookOpen className="w-8 h-8 mx-auto mb-3 opacity-20" />
                    <p>No subjects assigned for {session?.name} {term?.name} yet.</p>
                  </td>
                </tr>
              ) : (
                subjects.map((subject, idx) => (
                  <motion.tr 
                    initial={{ opacity: 0, x: -10 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: idx * 0.05 }}
                    key={subject.id} 
                    className="hover:bg-slate-50/50 transition-colors"
                  >
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded bg-royal-blue/5 flex items-center justify-center text-royal-blue shrink-0">
                          <BookOpen className="w-4 h-4" />
                        </div>
                        <span className="font-semibold text-slate-800">{subject.name}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button 
                        onClick={() => handleDelete(subject.id)}
                        title="Remove Subject"
                        className="p-2 text-slate-400 hover:text-red-600 transition-colors rounded-md hover:bg-red-50"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </motion.tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default Subjects;
