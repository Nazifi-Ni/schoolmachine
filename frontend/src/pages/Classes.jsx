import React, { useState, useEffect } from 'react';
import { BookOpen, Plus, Edit2, Trash2, Search, ArrowUpCircle } from 'lucide-react';
import { motion } from 'framer-motion';
import api from '../api';

import Modal from '../components/ui/Modal';

const Classes = () => {
  const [classes, setClasses] = useState([]);
  const [teachers, setTeachers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  // Modal state
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingClass, setEditingClass] = useState(null);
  const [formData, setFormData] = useState({ name: '', level: '', teacher_id: '' });

  const [isPromoteModalOpen, setIsPromoteModalOpen] = useState(false);
  const [promotingClass, setPromotingClass] = useState(null);
  const [targetClassId, setTargetClassId] = useState('');

  const openPromoteModal = (cls) => {
    setPromotingClass(cls);
    setTargetClassId('');
    setIsPromoteModalOpen(true);
  };

  const handlePromote = async (e) => {
    e.preventDefault();
    if (!targetClassId) return alert('Select target class');
    try {
      const res = await api.put(`/classes/${promotingClass.id}/promote`, { target_class_id: targetClassId });
      alert(`Successfully promoted ${res.data.promoted} students to the new class!`);
      setIsPromoteModalOpen(false);
    } catch(err) {
      alert(err.response?.data?.error || 'Failed to promote');
    }
  };

  const fetchClasses = async () => {
    try {
      const response = await api.get('/classes');
      setClasses(response.data.classes || []);
      setTeachers(response.data.teachers || []);
    } catch (err) {
      console.error('Failed to fetch classes', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchClasses();
  }, []);

  const handleDelete = async (id) => {
    if (await window.confirmAction('Are you sure you want to delete this class? This action might fail if there are students in it.')) {
      try {
        await api.delete(`/classes/${id}`);
        fetchClasses();
      } catch (err) {
        alert(err.response?.data?.error || 'Failed to delete class.');
      }
    }
  };

  const openAddModal = () => {
    setEditingClass(null);
    setFormData({ name: '', level: '', teacher_id: '' });
    setIsModalOpen(true);
  };

  const openEditModal = (cls) => {
    setEditingClass(cls);
    setFormData({
      name: cls.name,
      level: cls.level,
      teacher_id: cls.teacher_id || ''
    });
    setIsModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingClass) {
        await api.put(`/classes/${editingClass.id}`, formData);
      } else {
        await api.post('/classes', formData);
      }
      setIsModalOpen(false);
      fetchClasses();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to save class');
    }
  };

  const filteredClasses = classes.filter(c => 
    c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    c.level.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Classes</h1>
          <p className="text-sm text-slate-500 mt-1">Manage all classes and assign class teachers.</p>
        </div>
        <button onClick={openAddModal} className="glass-button flex items-center gap-2">
          <Plus className="w-4 h-4" />
          <span>Add Class</span>
        </button>
      </div>

      <div className="glass-card overflow-hidden flex flex-col">
        <div className="p-4 border-b border-slate-200 bg-white/50 flex justify-between items-center">
          <div className="relative w-64">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input 
              type="text" 
              placeholder="Search classes..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-9 pr-4 py-2 bg-white/60 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-royal-blue/20 focus:border-royal-blue transition-all w-full"
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600">
            <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="px-6 py-4 w-16">#</th>
                <th className="px-6 py-4">Class Name</th>
                <th className="px-6 py-4">Level</th>
                <th className="px-6 py-4">Class Teacher</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-slate-400">Loading...</td>
                </tr>
              ) : filteredClasses.length === 0 ? (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-slate-400">No classes found.</td>
                </tr>
              ) : (
                filteredClasses.map((cls, idx) => (
                  <motion.tr 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: idx * 0.05 }}
                    key={cls.id} 
                    className="hover:bg-slate-50/50 transition-colors"
                  >
                    <td className="px-6 py-4 font-medium text-slate-400">{idx + 1}</td>
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded bg-blue-50 flex items-center justify-center text-blue-500">
                          <BookOpen className="w-4 h-4" />
                        </div>
                        <span className="font-semibold text-slate-800">{cls.name}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                        {cls.level}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      {cls.first_name ? `${cls.first_name} ${cls.last_name}` : <span className="text-slate-400 italic">Unassigned</span>}
                    </td>
                    <td className="px-6 py-4 text-right space-x-2">
                      <button 
                        onClick={() => openPromoteModal(cls)}
                        title="Promote Students"
                        className="p-1.5 px-3 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 transition-colors rounded-md inline-flex items-center gap-1"
                      >
                        <ArrowUpCircle className="w-3.5 h-3.5" /> Promote
                      </button>

                      <button onClick={() => openEditModal(cls)} className="p-1.5 text-slate-400 hover:text-royal-blue transition-colors rounded-md hover:bg-royal-blue/10">
                        <Edit2 className="w-4 h-4" />
                      </button>
                      <button 
                        onClick={() => handleDelete(cls.id)}
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

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title={editingClass ? 'Edit Class' : 'Add Class'}>
        <form onSubmit={handleSave} className="space-y-4">
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1">Class Name</label>
            <input type="text" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} placeholder="e.g. Primary 1A" className="w-full glass-input" />
          </div>
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1">Level / Grade</label>
            <input type="text" required value={formData.level} onChange={e => setFormData({...formData, level: e.target.value})} placeholder="e.g. Primary 1" className="w-full glass-input" />
          </div>
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1">Class Teacher</label>
            <select value={formData.teacher_id} onChange={e => setFormData({...formData, teacher_id: e.target.value})} className="w-full glass-input">
              <option value="">-- Unassigned --</option>
              {teachers.map(t => (
                <option key={t.id} value={t.id}>{t.first_name} {t.last_name}</option>
              ))}
            </select>
          </div>

          <div className="pt-4 flex justify-end gap-2">
            <button type="button" onClick={() => setIsModalOpen(false)} className="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            <button type="submit" className="glass-button">Save Class</button>
          </div>
        </form>
      </Modal>

      <Modal isOpen={isPromoteModalOpen} onClose={() => setIsPromoteModalOpen(false)} title="Promote Students">
        <form onSubmit={handlePromote} className="space-y-4">
          <div className="p-4 bg-blue-50 text-royal-blue rounded-xl text-sm border border-blue-100">
            <strong>Action:</strong> Promote all active students in <strong>{promotingClass?.name}</strong> to a new class for the upcoming session.
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Target Class</label>
            <select required value={targetClassId} onChange={e => setTargetClassId(e.target.value)} className="w-full glass-input">
              <option value="">-- Select Target Class --</option>
              {classes.filter(c => c.id !== promotingClass?.id).map(c => (
                <option key={c.id} value={c.id}>{c.name} ({c.level})</option>
              ))}
            </select>
          </div>
          <div className="flex justify-end gap-3 pt-4">
            <button type="button" onClick={() => setIsPromoteModalOpen(false)} className="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
            <button type="submit" className="px-4 py-2 bg-royal-blue text-white rounded-lg hover:bg-blue-700">Promote Students</button>
          </div>
        </form>
      </Modal>
    </div>
  );
};

export default Classes;
