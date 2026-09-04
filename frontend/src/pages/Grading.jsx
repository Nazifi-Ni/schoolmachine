import React, { useState, useEffect } from 'react';
import { Award, Plus, Trash2 } from 'lucide-react';
import { motion } from 'framer-motion';
import api from '../api';

const Grading = () => {
  const [grades, setGrades] = useState([]);
  const [loading, setLoading] = useState(true);
  const [newGrade, setNewGrade] = useState({ min_score: '', max_score: '', grade: '', remark: '' });

  const fetchGrades = async () => {
    try {
      const response = await api.get('/grading');
      setGrades(response.data.grades || []);
    } catch (err) {
      console.error('Failed to fetch grades', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchGrades();
  }, []);

  const handleAddGrade = async (e) => {
    e.preventDefault();
    if (!newGrade.grade || newGrade.min_score === '' || newGrade.max_score === '') return;

    try {
      await api.post('/grading', newGrade);
      setNewGrade({ min_score: '', max_score: '', grade: '', remark: '' });
      fetchGrades();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to add grade');
    }
  };

  const handleDelete = async (id) => {
    if (await window.confirmAction('Delete this grade scale?')) {
      try {
        await api.delete(`/grading/${id}`);
        fetchGrades();
      } catch (err) {
        alert('Failed to delete');
      }
    }
  };

  return (
    <div className="space-y-6 max-w-5xl mx-auto">
      <div>
        <h1 className="text-2xl font-bold text-slate-800">Grading System</h1>
        <p className="text-sm text-slate-500 mt-1">Configure score ranges and corresponding grades.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {/* Add Grade Form */}
        <div className="md:col-span-1">
          <div className="glass-card p-6">
            <h2 className="font-semibold text-slate-700 mb-4 flex items-center gap-2">
              <Plus className="w-5 h-5 text-royal-blue" />
              Add Grade Level
            </h2>
            <form onSubmit={handleAddGrade} className="space-y-4">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-600 mb-1">Min Score</label>
                  <input
                    type="number"
                    value={newGrade.min_score}
                    onChange={(e) => setNewGrade({ ...newGrade, min_score: e.target.value })}
                    className="w-full glass-input"
                    required
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-600 mb-1">Max Score</label>
                  <input
                    type="number"
                    value={newGrade.max_score}
                    onChange={(e) => setNewGrade({ ...newGrade, max_score: e.target.value })}
                    className="w-full glass-input"
                    required
                  />
                </div>
              </div>
              
              <div>
                <label className="block text-xs font-medium text-slate-600 mb-1">Grade (e.g. A, B)</label>
                <input
                  type="text"
                  value={newGrade.grade}
                  onChange={(e) => setNewGrade({ ...newGrade, grade: e.target.value })}
                  className="w-full glass-input"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-600 mb-1">Remark (e.g. Excellent)</label>
                <input
                  type="text"
                  value={newGrade.remark}
                  onChange={(e) => setNewGrade({ ...newGrade, remark: e.target.value })}
                  className="w-full glass-input"
                />
              </div>

              <button type="submit" className="glass-button w-full mt-2">
                Save Grade
              </button>
            </form>
          </div>
        </div>

        {/* Grades Table */}
        <div className="md:col-span-2">
          <div className="glass-card overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-600 min-w-[400px]">
                <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                  <tr>
                    <th className="px-6 py-4">Score Range</th>
                    <th className="px-6 py-4">Grade</th>
                    <th className="px-6 py-4">Remark</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {loading ? (
                    <tr>
                      <td colSpan="4" className="px-6 py-8 text-center text-slate-400">Loading...</td>
                    </tr>
                  ) : grades.length === 0 ? (
                    <tr>
                      <td colSpan="4" className="px-6 py-8 text-center text-slate-400">No grading scale configured.</td>
                    </tr>
                  ) : (
                    grades.map((grade, idx) => (
                      <motion.tr 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: idx * 0.05 }}
                        key={grade.id} 
                        className="hover:bg-slate-50/50 transition-colors"
                      >
                        <td className="px-6 py-4 font-mono font-medium text-slate-600">
                          {grade.min_score} - {grade.max_score}
                        </td>
                        <td className="px-6 py-4">
                          <span className="w-8 h-8 rounded-lg bg-royal-blue/10 text-royal-blue flex items-center justify-center font-bold">
                            {grade.grade}
                          </span>
                        </td>
                        <td className="px-6 py-4">{grade.remark || '-'}</td>
                        <td className="px-6 py-4 text-right">
                          <button 
                            onClick={() => handleDelete(grade.id)}
                            className="p-1.5 text-slate-400 hover:text-red-600 transition-colors rounded-md hover:bg-red-50"
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
      </div>
    </div>
  );
};

export default Grading;
