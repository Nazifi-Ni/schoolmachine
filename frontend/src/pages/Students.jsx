import React, { useState, useEffect } from 'react';
import { Users, Plus, Edit2, Trash2, Search, Filter, Upload } from 'lucide-react';
import { motion } from 'framer-motion';
import api from '../api';

import Modal from '../components/ui/Modal';

const Students = () => {
  const [students, setStudents] = useState([]);
  const [classes, setClasses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [classFilter, setClassFilter] = useState('');

  console.log("Students.jsx is rendering!");

  // Modal state
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isBulkOpen, setIsBulkOpen] = useState(false);
  const [bulkText, setBulkText] = useState('');
  const [bulkClassId, setBulkClassId] = useState('');
  const [editingStudent, setEditingStudent] = useState(null);
  const [formData, setFormData] = useState({
    registration_number: '',
    surname: '',
    first_name: '',
    middle_name: '',
    gender: 'Male',
    dob: '',
    parent_name: '',
    phone: '',
    address: '',
    current_class_id: '',
    status: 'active'
  });

  const fetchStudents = async () => {
    try {
      const response = await api.get(`/students${classFilter ? `?class_id=${classFilter}` : ''}`);
      setStudents(response.data.students || []);
      if (!classFilter) setClasses(response.data.classes || []);
    } catch (err) {
      console.error('Failed to fetch students', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchStudents();
  }, [classFilter]);

  const handleDelete = async (id) => {
    if (await window.confirmAction('Are you sure you want to delete this student and ALL their academic records?')) {
      try {
        await api.delete(`/students/${id}`);
        fetchStudents();
      } catch (err) {
        alert(err.response?.data?.error || 'Failed to delete student.');
      }
    }
  };

  const openAddModal = () => {
    setEditingStudent(null);
    setFormData({
      registration_number: '', surname: '', first_name: '', middle_name: '', gender: 'Male', dob: '', parent_name: '', phone: '', address: '', current_class_id: classFilter || '', status: 'active'
    });
    setIsModalOpen(true);
  };

  const openEditModal = (student) => {
    setEditingStudent(student);
    setFormData({
      registration_number: student.registration_number || '',
      surname: student.surname,
      first_name: student.first_name,
      middle_name: student.middle_name || '',
      gender: student.gender || 'Male',
      dob: student.dob || '',
      parent_name: student.parent_name || '',
      phone: student.phone || '',
      address: student.address || '',
      current_class_id: student.current_class_id || '',
      status: student.status || 'active'
    });
    setIsModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingStudent) {
        await api.put(`/students/${editingStudent.id}`, formData);
      } else {
        await api.post('/students', formData);
      }
      setIsModalOpen(false);
      fetchStudents();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to save student');
    }
  };

  const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (evt) => {
      setBulkText(evt.target.result);
    };
    reader.readAsText(file);
  };

  const handleBulkImport = async (e) => {
    e.preventDefault();
    if (!bulkClassId) return alert('Please select a class');
    
    try {
      // Parse TSV or CSV
      const rows = bulkText.split('\n').filter(r => r.trim() !== '');
      const parsedStudents = [];
      
      for (let row of rows) {
        let cols = row.split(/\t|,/).map(c => c.trim()).filter(c => c !== '');
        
        // Fallback to space splitting if no tabs or commas
        if (cols.length < 2) {
          cols = row.split(/\s+/).map(c => c.trim()).filter(c => c !== '');
        }
        
        if (cols.length >= 2) {
          const lastCol = cols[cols.length - 1].toLowerCase();
          let gender = 'Male';
          let surname = '';
          let first_name = cols[0];
          
          if (lastCol === 'male' || lastCol === 'm' || lastCol === 'female' || lastCol === 'f') {
            gender = (lastCol === 'female' || lastCol === 'f') ? 'Female' : 'Male';
            surname = cols.slice(1, cols.length - 1).join(' ');
          } else {
            surname = cols.slice(1).join(' ');
          }

          if (!surname) surname = first_name; // fallback if somehow empty

          parsedStudents.push({
            first_name,
            surname,
            gender
          });
        }
      }
      
      if (parsedStudents.length === 0) {
        return alert('No valid students found to import. Please check your format.');
      }
      
      if (!await window.confirmAction(`Found ${parsedStudents.length} students. Proceed with import?`)) return;

      const res = await api.post('/students/bulk', { class_id: bulkClassId, students: parsedStudents });
      alert(`Successfully imported ${res.data.imported} students!`);
      setIsBulkOpen(false);
      setBulkText('');
      fetchStudents();
    } catch (err) {
      console.error(err);
      alert(err.response?.data?.error || err.message || 'Failed to import. Please try again.');
    }
  };

  const filteredStudents = students.filter(s => 
    `${s.first_name} ${s.surname}`.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (s.registration_number && s.registration_number.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Students</h1>
          <p className="text-sm text-slate-500 mt-1">Manage school enrollment and student profiles.</p>
        </div>
        <div className="flex gap-2">
          <button onClick={() => setIsBulkOpen(true)} className="glass-button bg-white text-slate-700 flex items-center gap-2">
            <Upload className="w-4 h-4" />
            <span>Bulk Import</span>
          </button>
          <button onClick={openAddModal} className="glass-button flex items-center gap-2">
            <Plus className="w-4 h-4" />
            <span>Register Student</span>
          </button>
        </div>
      </div>

      <div className="glass-card overflow-hidden flex flex-col">
        <div className="p-4 border-b border-slate-200 bg-white/50 flex flex-col sm:flex-row justify-between items-center gap-4">
          <div className="relative w-full sm:w-64">
            <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input 
              type="text" 
              placeholder="Search students..." 
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-9 pr-4 py-2 bg-white/60 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-royal-blue/20 focus:border-royal-blue transition-all w-full"
            />
          </div>
          
          <div className="flex items-center gap-2 w-full sm:w-auto">
            <Filter className="w-4 h-4 text-slate-400" />
            <select 
              value={classFilter}
              onChange={(e) => setClassFilter(e.target.value)}
              className="bg-white/60 border border-slate-200 rounded-lg text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-royal-blue/20 w-full sm:w-auto"
            >
              <option value="">All Classes</option>
              {classes.map(c => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-slate-600">
            <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="px-6 py-4">Reg No.</th>
                <th className="px-6 py-4">Name</th>
                <th className="px-6 py-4">Class</th>
                <th className="px-6 py-4">Gender</th>
                <th className="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-slate-400">Loading...</td>
                </tr>
              ) : filteredStudents.length === 0 ? (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-slate-400">No students found.</td>
                </tr>
              ) : (
                filteredStudents.map((student, idx) => (
                  <motion.tr 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: idx * 0.02 }}
                    key={student.id} 
                    className="hover:bg-slate-50/50 transition-colors"
                  >
                    <td className="px-6 py-4 font-mono text-xs font-medium text-slate-500">
                      {student.registration_number || 'N/A'}
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs uppercase shadow-sm">
                          {student.first_name[0]}{student.surname[0]}
                        </div>
                        <div>
                          <p className="font-semibold text-slate-800">{student.first_name} {student.surname}</p>
                          {student.status === 'inactive' && (
                             <span className="text-xs text-red-500 font-medium">Inactive</span>
                          )}
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-royal-blue/10 text-royal-blue border border-royal-blue/20">
                        {student.class_name}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      {student.gender}
                    </td>
                    <td className="px-6 py-4 text-right space-x-2">
                      <button onClick={() => openEditModal(student)} className="p-1.5 text-slate-400 hover:text-royal-blue transition-colors rounded-md hover:bg-royal-blue/10">
                        <Edit2 className="w-4 h-4" />
                      </button>
                      <button 
                        onClick={() => handleDelete(student.id)}
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

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title={editingStudent ? 'Edit Student' : 'Register Student'}>
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Registration No.</label>
              <input type="text" value={formData.registration_number} onChange={e => setFormData({...formData, registration_number: e.target.value})} className="w-full glass-input" />
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Class</label>
              <select required value={formData.current_class_id} onChange={e => setFormData({...formData, current_class_id: e.target.value})} className="w-full glass-input">
                <option value="">-- Select Class --</option>
                {classes.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Surname</label>
              <input type="text" required value={formData.surname} onChange={e => setFormData({...formData, surname: e.target.value})} className="w-full glass-input" />
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">First Name</label>
              <input type="text" required value={formData.first_name} onChange={e => setFormData({...formData, first_name: e.target.value})} className="w-full glass-input" />
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Middle Name</label>
              <input type="text" value={formData.middle_name} onChange={e => setFormData({...formData, middle_name: e.target.value})} className="w-full glass-input" />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Gender</label>
              <select required value={formData.gender} onChange={e => setFormData({...formData, gender: e.target.value})} className="w-full glass-input">
                <option>Male</option>
                <option>Female</option>
              </select>
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Date of Birth</label>
              <input type="date" value={formData.dob} onChange={e => setFormData({...formData, dob: e.target.value})} className="w-full glass-input" />
            </div>
          </div>

          {editingStudent && (
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Status</label>
              <select required value={formData.status} onChange={e => setFormData({...formData, status: e.target.value})} className="w-full glass-input">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="graduated">Graduated</option>
              </select>
            </div>
          )}

          <div className="pt-4 border-t border-slate-200/60 mt-4">
            <h3 className="text-sm font-semibold text-slate-700 mb-3">Guardian Information</h3>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-medium text-slate-600 mb-1">Parent/Guardian Name</label>
                <input type="text" value={formData.parent_name} onChange={e => setFormData({...formData, parent_name: e.target.value})} className="w-full glass-input" />
              </div>
              <div>
                <label className="block text-xs font-medium text-slate-600 mb-1">Phone Number</label>
                <input type="text" value={formData.phone} onChange={e => setFormData({...formData, phone: e.target.value})} className="w-full glass-input" />
              </div>
            </div>
          </div>

          <div className="pt-4 flex justify-end gap-2">
            <button type="button" onClick={() => setIsModalOpen(false)} className="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            <button type="submit" className="glass-button">Save Student</button>
          </div>
        </form>
      </Modal>

      {/* Bulk Import Modal */}
      <Modal isOpen={isBulkOpen} onClose={() => setIsBulkOpen(false)} title="Bulk Import Students">
        <form onSubmit={handleBulkImport} className="space-y-4">
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1">Select Class *</label>
            <select required value={bulkClassId} onChange={e => setBulkClassId(e.target.value)} className="w-full glass-input">
              <option value="">-- Select Class --</option>
              {classes.map(c => (
                <option key={c.id} value={c.id}>{c.name}</option>
              ))}
            </select>
          </div>
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1">Upload CSV or Paste Data</label>
            <p className="text-[10px] text-slate-500 mb-2">Upload a CSV file or paste directly from Excel. Format: First Name, Surname, Gender (Male/Female)</p>
            
            <input 
              type="file" 
              accept=".csv,.txt" 
              onChange={handleFileUpload} 
              className="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-royal-blue/10 file:text-royal-blue hover:file:bg-royal-blue/20 mb-3"
            />
            
            <textarea 
              required
              rows={8} 
              value={bulkText} 
              onChange={e => setBulkText(e.target.value)}
              className="w-full glass-input font-mono text-xs whitespace-pre"
              placeholder="John&#9;Doe&#9;Male&#10;Jane&#9;Smith&#9;Female"
            ></textarea>
          </div>
          <div className="pt-4 flex justify-end gap-2">
            <button type="button" onClick={() => setIsBulkOpen(false)} className="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
            <button type="submit" className="glass-button">Import Data</button>
          </div>
        </form>
      </Modal>
    </div>
  );
};

export default Students;
