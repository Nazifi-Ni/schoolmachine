import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Users, UserSquare, BookOpen, Clock, Activity, FileText, Calendar, TrendingUp } from 'lucide-react';
import { motion } from 'framer-motion';
import api from '../api';

const Dashboard = () => {
  const navigate = useNavigate();
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDashboard = async () => {
      try {
        const response = await api.get('/dashboard');
        setDashboardData(response.data);
      } catch (err) {
        console.error('Failed to fetch dashboard data', err);
      } finally {
        setLoading(false);
      }
    };
    fetchDashboard();
  }, []);

  if (loading) {
    return <div className="p-8 text-center text-slate-500">Loading dashboard...</div>;
  }

  if (!dashboardData || !dashboardData.stats) {
    return (
      <div className="p-8 text-center">
        <div className="glass-card p-8 max-w-md mx-auto">
          <h2 className="text-xl font-bold text-slate-700 mb-2">Connection Error</h2>
          <p className="text-slate-500 mb-4">Unable to load dashboard data. The server may be waking up — please wait 30 seconds and refresh the page.</p>
          <button onClick={() => window.location.reload()} className="btn-primary px-6 py-2 rounded-lg">Refresh</button>
        </div>
      </div>
    );
  }

  const { stats, role, recentActivities } = dashboardData;

  const getRoleBadgeColor = () => {
    if (role === 'Head Teacher') return 'bg-blue-100 text-blue-800 border-blue-200';
    if (role === 'Class Teacher') return 'bg-emerald-100 text-emerald-800 border-emerald-200';
    return 'bg-slate-100 text-slate-800 border-slate-200';
  };

  return (
    <div className="space-y-6">
      {/* Welcome Section */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 bg-gradient-to-r from-royal-blue to-blue-600 p-8 rounded-2xl shadow-lg relative overflow-hidden border border-blue-500/30">
        {/* Decorative background overlay */}
        <div className="absolute -top-24 -right-12 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div className="absolute -bottom-12 right-20 w-40 h-40 bg-blue-400/20 rounded-full blur-2xl pointer-events-none"></div>
        
        <div className="relative z-10">
          <h1 className="text-3xl font-bold text-white tracking-tight">Welcome back!</h1>
          <p className="text-blue-100 mt-1.5 font-medium">Here is what's happening in your school today.</p>
        </div>
        <div className={`relative z-10 px-4 py-1.5 rounded-full border shadow-sm text-sm font-bold tracking-wide uppercase ${
          role === 'Head Teacher' ? 'bg-white text-royal-blue border-transparent' : 
          'bg-emerald-500 text-white border-transparent'
        }`}>
          {role}
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {role === 'Head Teacher' ? (
          <>
            <StatCard icon={Users} label="Total Students" value={stats.total_students} color="blue" onClick={() => navigate('/students')} />
            <StatCard icon={UserSquare} label="Total Teachers" value={stats.total_teachers} color="indigo" onClick={() => navigate('/teachers')} />
            <StatCard icon={BookOpen} label="Total Classes" value={stats.total_classes} color="purple" onClick={() => navigate('/classes')} />
            <div className="glass-card p-5 flex flex-col justify-center cursor-pointer hover:shadow-lg transition-all" onClick={() => navigate('/sessions')}>
              <p className="text-sm font-medium text-slate-500 mb-1">Academic Session</p>
              <p className="font-bold text-slate-800">{stats.current_session}</p>
              <p className="text-xs text-royal-blue mt-1 font-medium">{stats.current_term}</p>
            </div>
          </>
        ) : (
          <>
            <StatCard icon={BookOpen} label="My Class" value={stats.class_name} color="blue" onClick={() => navigate('/my-class')} />
            <StatCard icon={Users} label="My Students" value={stats.total_students} color="indigo" onClick={() => navigate('/my-class')} />
            <StatCard icon={Clock} label="Pending Results" value={stats.pending_results} color="orange" onClick={() => navigate('/my-class')} />
          </>
        )}
      </div>

      {/* Main Content Area */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div className="lg:col-span-2 space-y-6">
          <div className="glass-card p-6">
            <h2 className="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
              <Activity className="w-5 h-5 text-royal-blue" />
              Quick Actions
            </h2>
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
              {role === 'Head Teacher' ? (
                <>
                  <QuickAction title="Add Student" desc="Register a new student" icon={Users} color="blue" onClick={() => navigate('/students')} />
                  <QuickAction title="Assign Teacher" desc="Manage class teachers" icon={UserSquare} color="indigo" onClick={() => navigate('/teachers')} />
                  <QuickAction title="New Session" desc="Start academic year" icon={Calendar} color="purple" onClick={() => navigate('/sessions')} />
                </>
              ) : (
                <>
                  <QuickAction title="Enter Marks" desc="Record student CA/Exams" icon={FileText} color="emerald" onClick={() => navigate('/my-class')} />
                  <QuickAction title="View Class" desc="See student list" icon={Users} color="blue" onClick={() => navigate('/my-class')} />
                </>
              )}
            </div>
          </div>
        </div>

        {/* Right Sidebar - Recent Activity */}
        <div className="space-y-6">
          <div className="glass-card p-6">
            <h2 className="text-base font-bold text-slate-800 mb-4">Recent Activity</h2>
            <div className="space-y-4">
              {(!recentActivities || recentActivities.length === 0) ? (
                <p className="text-sm text-slate-400 text-center py-4">No recent activity.</p>
              ) : (
                recentActivities.map((act, i) => (
                  <ActivityItem 
                    key={act.id || i}
                    title={act.title}
                    desc={act.description}
                    time={new Date(act.date).toLocaleDateString()}
                    type={act.type}
                  />
                ))
              )}
            </div>
          </div>
        </div>

      </div>
    </div>
  );
};

const StatCard = ({ icon: Icon, label, value, color, onClick }) => {
  const colorMap = {
    blue: 'bg-blue-50 text-blue-500',
    indigo: 'bg-indigo-50 text-indigo-500',
    purple: 'bg-purple-50 text-purple-500',
    emerald: 'bg-emerald-50 text-emerald-500',
    orange: 'bg-orange-50 text-orange-500',
  };

  return (
    <motion.div 
      whileHover={{ y: -2 }}
      onClick={onClick}
      className="glass-card p-5 flex items-center gap-4 hover:shadow-lg transition-all cursor-pointer"
    >
      <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${colorMap[color]}`}>
        <Icon className="w-6 h-6" />
      </div>
      <div>
        <p className="text-sm font-medium text-slate-500">{label}</p>
        <p className="text-2xl font-bold text-slate-800 tracking-tight">{value}</p>
      </div>
    </motion.div>
  );
};

const QuickAction = ({ title, desc, icon: Icon, color, onClick }) => {
  const colorMap = {
    blue: 'hover:bg-blue-50 hover:border-blue-200 group-hover:text-blue-500',
    indigo: 'hover:bg-indigo-50 hover:border-indigo-200 group-hover:text-indigo-500',
    purple: 'hover:bg-purple-50 hover:border-purple-200 group-hover:text-purple-500',
    emerald: 'hover:bg-emerald-50 hover:border-emerald-200 group-hover:text-emerald-500',
  };

  return (
    <button onClick={onClick} className={`p-4 rounded-xl border border-slate-100 bg-white/40 text-left transition-all group ${colorMap[color].split(' ')[0]} ${colorMap[color].split(' ')[1]}`}>
      <Icon className={`w-6 h-6 text-slate-400 mb-3 transition-colors ${colorMap[color].split(' ')[2]}`} />
      <h3 className="font-semibold text-slate-700 text-sm">{title}</h3>
      <p className="text-xs text-slate-500 mt-1">{desc}</p>
    </button>
  );
};

const ActivityItem = ({ title, desc, time, type }) => {
  return (
    <div className="flex gap-3">
      <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
        <div className="w-2 h-2 rounded-full bg-slate-400"></div>
      </div>
      <div>
        <p className="text-sm font-semibold text-slate-700">{title}</p>
        <p className="text-xs text-slate-500 mt-0.5">{desc}</p>
        <p className="text-[10px] font-medium text-slate-400 mt-1 uppercase tracking-wider">{time}</p>
      </div>
    </div>
  );
};

export default Dashboard;
