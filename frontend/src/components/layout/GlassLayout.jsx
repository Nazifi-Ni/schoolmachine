import React, { useEffect, useState } from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router-dom';
import { LayoutDashboard, Users, User, UserSquare, LogOut, Bell, Search, Menu, BookOpen, Calendar, Award, FileText, Wallet, Settings, Inbox, Clock } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import api from '../../api';

const GlassLayout = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [openMenus, setOpenMenus] = useState({ Settings: false });
  
  const [notifications, setNotifications] = useState([]);
  const [showNotifications, setShowNotifications] = useState(false);

  useEffect(() => {
    const checkAuth = async () => {
      try {
        const response = await api.get(`/me?t=${Date.now()}`);
        const userData = response.data.user;
        if (userData.role === 'Student') {
          console.warn('GlassLayout: User is a Student, redirecting to /student-portal');
          alert('System detected a Student session. Redirecting to Student Portal. Please Logout from the Student Portal to clear this.');
          navigate('/student-portal');
        } else {
          setUser(userData);
        }
      } catch (err) {
        navigate('/login');
      }
    };
    checkAuth();
  }, [navigate]);

  useEffect(() => {
    // Only fetch notifications if user is logged in
    if (!user) return;
    const fetchNotifications = async () => {
      try {
        const response = await api.get('/dashboard');
        // Filter or just use recentActivities
        if (response.data.recentActivities) {
          setNotifications(response.data.recentActivities);
        }
      } catch (err) {
        console.error('Failed to fetch notifications', err);
      }
    };
    fetchNotifications();
  }, [user]);

  const handleLogout = async () => {
    try {
      await api.post('/logout');
      navigate('/login');
    } catch (err) {
      console.error('Logout failed', err);
    }
  };

  const navItems = [
    { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard },
    { name: 'Teachers', path: '/teachers', icon: UserSquare, role: 'Head Teacher' },
    { name: 'Classes', path: '/classes', icon: BookOpen, role: 'Head Teacher' },
    { name: 'Students', path: '/students', icon: Users, role: 'Head Teacher' },
    { name: 'Admissions', path: '/admissions', icon: Inbox, role: 'Head Teacher' },
    { name: 'My Class', path: '/my-class', icon: FileText, role: 'Class Teacher' },
    { name: 'Manage Subjects', path: '/my-class/subjects', icon: BookOpen, role: 'Class Teacher' },
    { name: 'Student Billing', path: '/finance/bills', icon: Wallet, role: 'Head Teacher' },
    { name: 'Payment Approvals', path: '/finance/approvals', icon: Clock, role: 'Head Teacher' },
    { 
      name: 'Settings', 
      icon: Settings, 
      role: 'Head Teacher',
      children: [
        { name: 'Fee Setup', path: '/finance/setup' },
        { name: 'Sessions & Terms', path: '/sessions' },
        { name: 'Grading System', path: '/grading' },
        { name: 'Change Password', path: '/settings/password' }
      ]
    }
  ];

  const toggleMenu = (name) => {
    setOpenMenus(prev => ({ ...prev, [name]: !prev[name] }));
  };

  const renderNavItems = (onItemClick = () => {}) => (
    navItems.filter(item => !item.role || item.role === user.role).map((item) => {
      const Icon = item.icon;
      
      if (item.children) {
        const isChildActive = item.children.some(child => location.pathname.startsWith(child.path));
        const isOpen = openMenus[item.name] || isChildActive;
        
        return (
          <div key={item.name} className="mb-1">
            <button
              onClick={() => toggleMenu(item.name)}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-all group ${
                isChildActive ? 'bg-royal-blue/10 text-royal-blue' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
              }`}
            >
              <div className="flex items-center">
                <Icon className={`w-5 h-5 mr-3 ${isChildActive ? 'text-royal-blue' : 'text-slate-400 group-hover:text-slate-600'}`} />
                <span className="font-medium">{item.name}</span>
              </div>
              <svg className={`w-4 h-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            {isOpen && (
              <div className="ml-9 mt-1 space-y-1">
                {item.children.map(child => {
                  const isActive = location.pathname.startsWith(child.path);
                  return (
                    <Link
                      key={child.name}
                      to={child.path}
                      onClick={onItemClick}
                      className={`block px-3 py-2 rounded-lg text-sm transition-all ${
                        isActive 
                          ? 'bg-royal-blue text-white shadow-sm shadow-royal-blue/20 font-medium' 
                          : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100'
                      }`}
                    >
                      {child.name}
                    </Link>
                  );
                })}
              </div>
            )}
          </div>
        );
      }

      const isActive = location.pathname.startsWith(item.path);
      return (
        <Link
          key={item.name}
          to={item.path}
          onClick={onItemClick}
          className={`flex items-center px-3 py-2.5 rounded-lg transition-all group mb-1 ${
            isActive 
              ? 'bg-royal-blue text-white shadow-md shadow-royal-blue/20' 
              : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
          }`}
        >
          <Icon className={`w-5 h-5 mr-3 ${isActive ? 'text-white' : 'text-slate-400 group-hover:text-slate-600'}`} />
          <span className="font-medium">{item.name}</span>
        </Link>
      );
    })
  );

  if (!user) return <div className="h-screen bg-slate-50 flex items-center justify-center">Loading...</div>;

  return (
    <div className="h-screen bg-slate-50 flex overflow-hidden">
      <aside className="w-64 flex-shrink-0 border-r border-slate-200 bg-white/50 backdrop-blur-xl hidden md:flex flex-col z-10 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
        <div className="h-16 flex items-center px-6 border-b border-slate-200">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded bg-white shadow-sm border border-slate-100 p-1 flex items-center justify-center">
                <img src="/assets/images/school_logo.jpg" alt="Logo" className="w-full h-full object-contain" />
            </div>
            <span className="font-bold text-slate-800 text-lg tracking-tight">IAMS ARMS</span>
          </div>
        </div>

        <nav className="flex-1 overflow-y-auto py-6 px-4 space-y-1">
          <p className="px-2 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Main Menu</p>
          {renderNavItems()}
        </nav>

        <div className="p-4 border-t border-slate-200">
          <button onClick={handleLogout} className="flex items-center w-full px-3 py-2.5 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
            <LogOut className="w-5 h-5 mr-3 text-red-500" />
            Sign Out
          </button>
        </div>
      </aside>

      {mobileMenuOpen && (
        <div className="fixed inset-0 z-50 flex md:hidden">
          <div className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onClick={() => setMobileMenuOpen(false)}></div>
          <motion.aside 
            initial={{ x: '-100%' }} 
            animate={{ x: 0 }} 
            exit={{ x: '-100%' }}
            className="w-64 flex-shrink-0 bg-white shadow-2xl flex flex-col z-10"
          >
            <div className="h-16 flex items-center px-6 border-b border-slate-200">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded bg-white shadow-sm border border-slate-100 p-1 flex items-center justify-center">
                    <img src="/assets/images/school_logo.jpg" alt="Logo" className="w-full h-full object-contain" />
                </div>
                <span className="font-bold text-slate-800 text-lg tracking-tight">IAMS ARMS</span>
              </div>
            </div>

            <nav className="flex-1 overflow-y-auto py-6 px-4 space-y-1">
              {renderNavItems(() => setMobileMenuOpen(false))}
            </nav>

            <div className="p-4 border-t border-slate-200">
              <button onClick={handleLogout} className="flex items-center w-full px-3 py-2.5 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                <LogOut className="w-5 h-5 mr-3 text-red-500" />
                Sign Out
              </button>
            </div>
          </motion.aside>
        </div>
      )}

      <div className="flex-1 flex flex-col min-w-0 bg-slate-50 relative">
        <div className="absolute top-0 right-0 w-[600px] h-[400px] bg-royal-blue/5 rounded-full blur-3xl -z-10 pointer-events-none"></div>
        
        <header className="h-16 flex items-center justify-between px-6 bg-white/40 backdrop-blur-xl border-b border-white/60 shadow-[0_4px_30px_rgba(0,0,0,0.03)] sticky top-0 z-50">
          <div className="flex items-center">
            <button 
              onClick={() => setMobileMenuOpen(true)}
              className="md:hidden mr-4 text-slate-500 hover:text-slate-700"
            >
              <Menu className="w-6 h-6" />
            </button>
            
            <div className="relative hidden sm:block">
              <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input 
                type="text" 
                placeholder="Search..." 
                className="pl-9 pr-4 py-2 bg-white/50 backdrop-blur-md border border-white/80 shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-royal-blue/20 focus:border-royal-blue transition-all w-64 text-slate-700 placeholder:text-slate-400"
              />
            </div>
          </div>

          <div className="flex items-center space-x-4">
            <div className="relative">
              <button 
                onClick={() => setShowNotifications(!showNotifications)}
                className="relative p-2 text-slate-400 hover:text-slate-600 transition-colors"
              >
                <Bell className="w-5 h-5" />
                {notifications.length > 0 && (
                  <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                )}
              </button>

              <AnimatePresence>
                {showNotifications && (
                  <motion.div 
                    initial={{ opacity: 0, y: 10, scale: 0.95 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    exit={{ opacity: 0, y: 10, scale: 0.95 }}
                    transition={{ duration: 0.2 }}
                    className="absolute right-0 mt-2 w-80 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50"
                  >
                    <div className="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                      <h3 className="font-bold text-slate-800">Notifications</h3>
                      <span className="text-xs font-semibold bg-royal-blue/10 text-royal-blue px-2 py-1 rounded-full">{notifications.length} New</span>
                    </div>
                    <div className="max-h-[300px] overflow-y-auto">
                      {notifications.length > 0 ? (
                        notifications.map((notif, idx) => (
                          <div key={idx} className="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-default">
                            <p className="text-sm font-bold text-slate-700">{notif.title}</p>
                            <p className="text-xs text-slate-500 mt-1">{notif.description}</p>
                            <p className="text-[10px] text-slate-400 mt-2 font-medium">{notif.date || 'Just now'}</p>
                          </div>
                        ))
                      ) : (
                        <div className="p-6 text-center text-slate-500 text-sm">
                          <Bell className="w-8 h-8 text-slate-300 mx-auto mb-2 opacity-50" />
                          No new notifications
                        </div>
                      )}
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
            
            <div className="flex items-center gap-3 border-l border-slate-200 pl-4">
              <div className="text-right hidden sm:block">
                <p className="text-sm font-medium text-slate-700 capitalize">
                  {user.first_name ? `${user.first_name} ${user.surname || ''}` : user.username || 'User'}
                </p>
                {user.username?.toLowerCase() !== user.role?.toLowerCase().replace(/\s/g, '') && (
                  <p className="text-xs text-slate-500">{user.role}</p>
                )}
              </div>
              <div className="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm border border-slate-200 overflow-hidden">
                <User className="w-5 h-5" />
              </div>
            </div>
          </div>
        </header>

        <main className="flex-1 overflow-auto p-6">
          <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.3 }}
          >
            <Outlet />
          </motion.div>
        </main>
      </div>
    </div>
  );
};

export default GlassLayout;
