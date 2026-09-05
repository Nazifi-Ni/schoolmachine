import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Save, AlertCircle, Activity, Printer } from 'lucide-react';
import { motion } from 'framer-motion';
import api from '../api';

const StudentResults = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  
  // Form State
  const [scores, setScores] = useState({});
  const [remarks, setRemarks] = useState({ class_teacher_remark: '', head_teacher_remark: '', head_teacher_name: '', attendance: '', resumption_date: '', past_balance: '', next_term_fee: '' });

  useEffect(() => {
    const fetchResults = async () => {
      try {
        const response = await api.get(`/my-class/students/${id}/results`);
        setData(response.data);
        
        // Initialize scores state
        const initialScores = {};
        response.data.subjects.forEach(sub => {
          const existing = response.data.resultItems[sub.id];
          initialScores[sub.id] = {
            ca1: existing?.ca1 || '',
            ca2: existing?.ca2 || '',
            exam: existing?.exam || ''
          };
        });
        setScores(initialScores);

        // Initialize remarks
        if (response.data.resultRecord) {
          setRemarks({
            class_teacher_remark: response.data.resultRecord.class_teacher_remark || '',
            head_teacher_remark: response.data.resultRecord.head_teacher_remark || '',
            head_teacher_name: response.data.resultRecord.head_teacher_name || '',
            attendance: response.data.resultRecord.attendance || '',
            resumption_date: response.data.resultRecord.resumption_date || '',
            past_balance: response.data.resultRecord.past_balance || '',
            next_term_fee: response.data.resultRecord.next_term_fee || ''
          });
        }
      } catch (err) {
        console.error('Failed to fetch student results', err);
        alert('Failed to load student data');
        navigate('/my-class');
      } finally {
        setLoading(false);
      }
    };
    fetchResults();
  }, [id, navigate]);

  const handleScoreChange = (subjectId, field, value) => {
    setScores(prev => ({
      ...prev,
      [subjectId]: {
        ...prev[subjectId],
        [field]: value
      }
    }));
  };

  const handleAutoGenerate = () => {
    let totals = [];
    Object.values(scores).forEach(s => {
      const total = (parseFloat(s.ca1) || 0) + (parseFloat(s.ca2) || 0) + (parseFloat(s.exam) || 0);
      if (total > 0) totals.push(total);
    });

    if (totals.length === 0) {
      alert("Please enter some scores first!");
      return;
    }

    const avg = totals.reduce((a, b) => a + b, 0) / totals.length;
    const name = data?.student?.first_name || "This student";
    let ct_remark = "";
    let ht_remark = "";

    if (avg >= 80) {
        ct_remark = `${name} has shown an outstanding performance this term. Keep up the excellent work!`;
        ht_remark = `An excellent result. I am very proud of your hard work, ${name}.`;
    } else if (avg >= 70) {
        ct_remark = `A very good result. I believe ${name} has the potential to do even better next term.`;
        ht_remark = `Very good performance. Keep aiming higher, ${name}.`;
    } else if (avg >= 60) {
        ct_remark = `A good result. With a little more focus, ${name} can achieve even greater grades.`;
        ht_remark = `Good effort. Keep working hard to maximize your potential.`;
    } else if (avg >= 50) {
        ct_remark = `A fair attempt. ${name} is encouraged to dedicate more time to studies to improve.`;
        ht_remark = `Fair result. We believe in your ability to do much better, ${name}.`;
    } else if (avg >= 40) {
        ct_remark = `${name} has the ability to succeed, but needs to put in more consistent effort next term.`;
        ht_remark = `We encourage ${name} to be more serious with studies to see better results.`;
    } else {
        ct_remark = `${name} requires extra support and attention to reach their full potential. Let's work together to improve this.`;
        ht_remark = `We recommend closer monitoring and encouragement for ${name} at home to aid better performance.`;
    }

    setRemarks(prev => ({
      ...prev,
      class_teacher_remark: ct_remark,
      head_teacher_remark: ht_remark
    }));
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      await api.post(`/my-class/students/${id}/results`, {
        scores,
        ...remarks
      });
      alert('Scores saved successfully!');
      navigate('/my-class');
    } catch (err) {
      alert(err.response?.data?.error || 'Failed to save scores');
    }
  };

  if (loading) return <div className="p-8 text-center text-slate-500">Loading student record...</div>;
  if (!data) return null;

  const { student, subjects, gradingSystem } = data;

  // Helper to calculate grade live
  const calculateLiveGrade = (ca1, ca2, exam) => {
    const total = (parseFloat(ca1) || 0) + (parseFloat(ca2) || 0) + (parseFloat(exam) || 0);
    if (total === 0) return '-';
    for (const g of gradingSystem) {
      if (total >= g.min_score && total <= g.max_score) return g.grade;
    }
    return '-';
  };

  return (
    <div className="space-y-6 max-w-6xl mx-auto">
      <div className="flex items-center gap-4 mb-6">
        <button 
          onClick={() => navigate('/my-class')}
          className="p-2 bg-white/60 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500 transition-colors"
        >
          <ArrowLeft className="w-5 h-5" />
        </button>
        <div>
          <h1 className="text-2xl font-bold text-slate-800">
            {student.first_name} {student.surname}
          </h1>
          <p className="text-sm text-slate-500 font-mono mt-1">Reg No: {student.registration_number}</p>
        </div>
      </div>

      <form onSubmit={handleSave} className="space-y-8">
        
        {/* Scores Table */}
        <div className="glass-card overflow-hidden">
          <div className="p-4 border-b border-slate-200/60 bg-white/40">
            <h2 className="font-semibold text-slate-700">Subject Scores</h2>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm text-slate-600">
              <thead className="bg-slate-50/50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                  <th className="px-6 py-4">Subject</th>
                  <th className="px-6 py-4 w-32">CA 1</th>
                  <th className="px-6 py-4 w-32">CA 2</th>
                  <th className="px-6 py-4 w-32">Exam</th>
                  <th className="px-6 py-4 w-24">Total</th>
                  <th className="px-6 py-4 w-24">Grade</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {subjects.length === 0 ? (
                  <tr>
                    <td colSpan="6" className="px-6 py-8 text-center text-slate-400">
                      <div className="flex flex-col items-center gap-2">
                        <AlertCircle className="w-6 h-6 text-orange-400" />
                        <p>No subjects assigned to this class yet.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  subjects.map((sub, idx) => {
                    const s = scores[sub.id] || {};
                    const total = (parseFloat(s.ca1) || 0) + (parseFloat(s.ca2) || 0) + (parseFloat(s.exam) || 0);
                    const grade = calculateLiveGrade(s.ca1, s.ca2, s.exam);
                    
                    return (
                      <motion.tr 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: idx * 0.02 }}
                        key={sub.id} 
                        className="hover:bg-slate-50/50 transition-colors"
                      >
                        <td className="px-6 py-4 font-medium text-slate-800">{sub.name}</td>
                        <td className="px-6 py-4">
                          <input 
                            type="number" 
                            min="0" max="20" step="0.1"
                            value={s.ca1}
                            onChange={(e) => handleScoreChange(sub.id, 'ca1', e.target.value)}
                            className="w-full bg-white border border-slate-200 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-royal-blue/20 outline-none"
                            placeholder="0-20"
                          />
                        </td>
                        <td className="px-6 py-4">
                          <input 
                            type="number" 
                            min="0" max="20" step="0.1"
                            value={s.ca2}
                            onChange={(e) => handleScoreChange(sub.id, 'ca2', e.target.value)}
                            className="w-full bg-white border border-slate-200 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-royal-blue/20 outline-none"
                            placeholder="0-20"
                          />
                        </td>
                        <td className="px-6 py-4">
                          <input 
                            type="number" 
                            min="0" max="60" step="0.1"
                            value={s.exam}
                            onChange={(e) => handleScoreChange(sub.id, 'exam', e.target.value)}
                            className="w-full bg-white border border-slate-200 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-royal-blue/20 outline-none"
                            placeholder="0-60"
                          />
                        </td>
                        <td className="px-6 py-4 font-bold text-slate-700">
                          {total > 0 ? total : '-'}
                        </td>
                        <td className="px-6 py-4">
                          <span className={`inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-sm ${grade !== '-' ? 'bg-royal-blue/10 text-royal-blue' : 'bg-slate-100 text-slate-400'}`}>
                            {grade}
                          </span>
                        </td>
                      </motion.tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* Remarks and Meta */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="glass-card p-6 space-y-4">
            <div className="flex items-center justify-between mb-2">
              <h3 className="font-semibold text-slate-700">Class Teacher's Remark</h3>
              <button 
                type="button" 
                onClick={handleAutoGenerate}
                className="text-xs bg-royal-blue/10 text-royal-blue px-2 py-1 rounded hover:bg-royal-blue/20 transition-colors font-medium flex items-center gap-1"
              >
                <Activity className="w-3 h-3" /> Auto-Generate
              </button>
            </div>
            <textarea
              rows="3"
              value={remarks.class_teacher_remark}
              onChange={e => setRemarks({...remarks, class_teacher_remark: e.target.value})}
              placeholder="Enter your remarks about the student's performance..."
              className="w-full glass-input"
            ></textarea>
            
            <h3 className="font-semibold text-slate-700 pt-2 mb-2">Head Teacher's Comment</h3>
            <textarea
              rows="3"
              value={remarks.head_teacher_remark}
              onChange={e => setRemarks({...remarks, head_teacher_remark: e.target.value})}
              placeholder="Head Teacher's remarks..."
              className="w-full glass-input"
            ></textarea>
          </div>

          <div class="glass-card p-6 space-y-4">
            <h3 className="font-semibold text-slate-700 mb-2">Attendance & Meta</h3>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Head Teacher's Name</label>
              <input 
                type="text" 
                value={remarks.head_teacher_name}
                onChange={e => setRemarks({...remarks, head_teacher_name: e.target.value})}
                placeholder="E.g. SADIQ SABO ABBA"
                className="w-full glass-input"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Days Present</label>
              <input 
                type="text" 
                value={remarks.attendance}
                onChange={e => setRemarks({...remarks, attendance: e.target.value})}
                placeholder="e.g. 110/114"
                className="w-full glass-input"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1">Next Term Resumes</label>
              <input 
                type="date" 
                value={remarks.resumption_date}
                onChange={e => setRemarks({...remarks, resumption_date: e.target.value})}
                className="w-full glass-input"
              />
            </div>
          </div>
        </div>

        <div className="flex justify-end gap-4 pt-4 pb-12">
          <button 
            type="button"
            onClick={async (e) => {
              e.preventDefault();
              try {
                await api.post(`/my-class/students/${id}/results`, { scores, ...remarks });
                const baseUrl = api.defaults.baseURL.replace('/api', '');
                const token = localStorage.getItem('auth_token');
                window.open(`${baseUrl}/results/print/${id}?token=${token}`, '_blank');
                navigate('/my-class');
              } catch (err) {
                alert(err.response?.data?.error || 'Failed to save scores');
              }
            }}
            className="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition-colors flex items-center gap-2"
          >
            <Printer className="w-5 h-5" /> Save & Print
          </button>

          <button type="submit" className="glass-button flex items-center gap-2 px-8 py-3 text-lg">
            <Save className="w-5 h-5" /> Save Results
          </button>
        </div>

      </form>
    </div>
  );
};

export default StudentResults;
