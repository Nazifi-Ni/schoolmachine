import React, { useState, useEffect } from 'react';
import { Calendar, Plus, CheckCircle, Trash2, Clock, ChevronDown, ChevronRight } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import api from '../api';
import Modal from '../components/ui/Modal';

const Sessions = () => {
  const [sessions, setSessions] = useState([]);
  const [terms, setTerms] = useState([]);
  const [loading, setLoading] = useState(true);

  const [isSessionModalOpen, setIsSessionModalOpen] = useState(false);
  const [isTermModalOpen, setIsTermModalOpen] = useState(false);
  const [newSessionName, setNewSessionName] = useState('');
  const [newTerm, setNewTerm] = useState({ name: '', session_id: '' });
  
  const [expandedSessions, setExpandedSessions] = useState({});

  const fetchData = async () => {
    try {
      const response = await api.get('/sessions');
      setSessions(response.data.sessions || []);
      setTerms(response.data.terms || []);
      
      // Auto-expand current session
      const current = response.data.sessions?.find(s => s.is_current);
      if (current) {
        setExpandedSessions(prev => ({ ...prev, [current.id]: true }));
      }
    } catch (err) {
      console.error('Failed to fetch sessions', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const toggleSession = (id) => {
    setExpandedSessions(prev => ({ ...prev, [id]: !prev[id] }));
  };

  const handleSetCurrentSession = async (id) => {
    try {
      await api.put(`/sessions/${id}/set-current`);
      fetchData();
    } catch (err) {
      alert('Failed to update current session');
    }
  };

  const handleDeleteSession = async (id) => {
    if (await window.confirmAction('Delete this session and all its terms?')) {
      try {
        await api.delete(`/sessions/${id}`);
        fetchData();
      } catch (err) {
        alert(err.response?.data?.error || 'Failed to delete');
      }
    }
  };

  const handleSetCurrentTerm = async (termId, sessionId) => {
    try {
      // Setting a term as current will implicitly set its parent session as current in the backend if we update the endpoint,
      // but let's just make two calls for UX safety, or one call if the backend handles it.
      await api.put(`/sessions/${sessionId}/set-current`);
      await api.put(`/terms/${termId}/set-current`);
      fetchData();
    } catch (err) {
      alert('Failed to update current term');
    }
  };

  const handleDeleteTerm = async (id) => {
    if (await window.confirmAction('Delete this term?')) {
      try {
        await api.delete(`/terms/${id}`);
        fetchData();
      } catch (err) {
        alert('Failed to delete');
      }
    }
  };

  const handleAddSession = async (e) => {
    e.preventDefault();
    if (!newSessionName.trim()) return;
    try {
      await api.post('/sessions', { name: newSessionName });
      setNewSessionName('');
      setIsSessionModalOpen(false);
      fetchData();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to add session');
    }
  };

  const handleAddTerm = async (e) => {
    e.preventDefault();
    if (!newTerm.name || !newTerm.session_id) return;
    try {
      await api.post('/terms', newTerm);
      setNewTerm({ name: '', session_id: '' });
      setIsTermModalOpen(false);
      fetchData();
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to add term');
    }
  };

  const openAddTermModal = (sessionId) => {
    setNewTerm({ name: '', session_id: sessionId });
    setIsTermModalOpen(true);
  };

  return (
    <div className="space-y-8 max-w-5xl mx-auto">
      <div className="flex justify-between items-end">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Academic Sessions & Terms</h1>
          <p className="text-sm text-slate-500 mt-1">Manage academic years and hierarchical terms.</p>
        </div>
        <button onClick={() => setIsSessionModalOpen(true)} className="px-4 py-2 bg-royal-blue text-white rounded-lg flex items-center gap-2 hover:bg-blue-700 transition-colors shadow-sm">
          <Plus className="w-4 h-4" /> New Session
        </button>
      </div>

      <div className="space-y-4">
        {loading ? (
          <p className="text-slate-400">Loading...</p>
        ) : sessions.length === 0 ? (
          <div className="p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
            <Calendar className="w-12 h-12 text-slate-300 mx-auto mb-3" />
            <p className="text-slate-500">No sessions found. Create one to get started.</p>
          </div>
        ) : (
          sessions.map((session) => {
            const sessionTerms = terms.filter(t => t.session_id === session.id);
            const isExpanded = expandedSessions[session.id];
            
            return (
              <div key={session.id} className={`bg-white rounded-2xl border transition-all duration-200 overflow-hidden ${session.is_current ? 'border-royal-blue/40 shadow-md shadow-royal-blue/5' : 'border-slate-200 shadow-sm'}`}>
                {/* Session Header */}
                <div 
                  className={`p-5 flex items-center justify-between cursor-pointer hover:bg-slate-50/50 ${session.is_current ? 'bg-blue-50/30' : ''}`}
                  onClick={() => toggleSession(session.id)}
                >
                  <div className="flex items-center gap-4">
                    <div className={`p-2 rounded-lg ${session.is_current ? 'bg-royal-blue/10 text-royal-blue' : 'bg-slate-100 text-slate-500'}`}>
                      <Calendar className="w-5 h-5" />
                    </div>
                    <div>
                      <div className="flex items-center gap-3">
                        <h2 className="text-lg font-bold text-slate-800">{session.name}</h2>
                        {session.is_current && (
                          <span className="px-2.5 py-0.5 rounded-full bg-royal-blue/10 text-royal-blue text-xs font-semibold flex items-center gap-1">
                            <CheckCircle className="w-3 h-3" /> Active
                          </span>
                        )}
                      </div>
                      <p className="text-sm text-slate-500 mt-0.5">{sessionTerms.length} Terms</p>
                    </div>
                  </div>

                  <div className="flex items-center gap-4">
                    {!session.is_current && (
                      <button 
                        onClick={(e) => { e.stopPropagation(); handleSetCurrentSession(session.id); }}
                        className="text-sm px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-royal-blue hover:border-royal-blue/30 transition-colors"
                      >
                        Set Active
                      </button>
                    )}
                    <button 
                      onClick={(e) => { e.stopPropagation(); handleDeleteSession(session.id); }}
                      className="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                    <div className="w-px h-6 bg-slate-200 mx-1"></div>
                    {isExpanded ? <ChevronDown className="w-5 h-5 text-slate-400" /> : <ChevronRight className="w-5 h-5 text-slate-400" />}
                  </div>
                </div>

                {/* Terms Area (Expanded) */}
                <AnimatePresence>
                  {isExpanded && (
                    <motion.div 
                      initial={{ height: 0, opacity: 0 }}
                      animate={{ height: 'auto', opacity: 1 }}
                      exit={{ height: 0, opacity: 0 }}
                      className="border-t border-slate-100 bg-slate-50/50"
                    >
                      <div className="p-5 pl-16">
                        <div className="flex justify-between items-center mb-4">
                          <h3 className="text-sm font-semibold text-slate-600 uppercase tracking-wider">Terms in {session.name}</h3>
                          <button onClick={() => openAddTermModal(session.id)} className="text-sm text-royal-blue hover:text-blue-700 font-medium flex items-center gap-1">
                            <Plus className="w-4 h-4" /> Add Term
                          </button>
                        </div>

                        {sessionTerms.length === 0 ? (
                          <p className="text-sm text-slate-400 py-4 italic">No terms added yet.</p>
                        ) : (
                          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {sessionTerms.map(term => (
                              <div key={term.id} className={`p-4 rounded-xl border bg-white flex flex-col justify-between ${term.is_current ? 'border-green-300 ring-2 ring-green-500/10' : 'border-slate-200 hover:border-slate-300'} transition-all`}>
                                <div>
                                  <div className="flex justify-between items-start">
                                    <h4 className="font-bold text-slate-800">{term.name}</h4>
                                    {term.is_current && (
                                      <span className="text-green-600 bg-green-50 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">
                                        Current
                                      </span>
                                    )}
                                  </div>
                                </div>
                                <div className="mt-4 flex gap-2 justify-end border-t border-slate-100 pt-3">
                                  {!term.is_current && (
                                    <button 
                                      onClick={() => handleSetCurrentTerm(term.id, session.id)} 
                                      className="text-xs font-medium text-slate-600 hover:text-royal-blue bg-slate-100 hover:bg-blue-50 px-2.5 py-1.5 rounded-md transition-colors flex-1 text-center"
                                    >
                                      Make Current
                                    </button>
                                  )}
                                  <button 
                                    onClick={() => handleDeleteTerm(term.id)} 
                                    className="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                                  >
                                    <Trash2 className="w-4 h-4" />
                                  </button>
                                </div>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            );
          })
        )}
      </div>

      {/* Add Session Modal */}
      <Modal isOpen={isSessionModalOpen} onClose={() => setIsSessionModalOpen(false)} title="Add Academic Session">
        <form onSubmit={handleAddSession} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Session Name</label>
            <input 
              type="text" 
              required 
              placeholder="e.g. 2025/2026"
              className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-royal-blue focus:border-royal-blue"
              value={newSessionName}
              onChange={e => setNewSessionName(e.target.value)}
            />
          </div>
          <div className="flex justify-end gap-3 pt-4">
            <button type="button" onClick={() => setIsSessionModalOpen(false)} className="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
            <button type="submit" className="px-4 py-2 bg-royal-blue text-white rounded-lg hover:bg-blue-700">Add Session</button>
          </div>
        </form>
      </Modal>

      {/* Add Term Modal */}
      <Modal isOpen={isTermModalOpen} onClose={() => setIsTermModalOpen(false)} title="Add Term">
        <form onSubmit={handleAddTerm} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Term Name</label>
            <input 
              type="text" 
              required 
              placeholder="e.g. First Term"
              className="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-royal-blue focus:border-royal-blue"
              value={newTerm.name}
              onChange={e => setNewTerm({...newTerm, name: e.target.value})}
            />
          </div>
          <div className="flex justify-end gap-3 pt-4">
            <button type="button" onClick={() => setIsTermModalOpen(false)} className="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
            <button type="submit" className="px-4 py-2 bg-royal-blue text-white rounded-lg hover:bg-blue-700">Add Term</button>
          </div>
        </form>
      </Modal>
    </div>
  );
};

export default Sessions;
