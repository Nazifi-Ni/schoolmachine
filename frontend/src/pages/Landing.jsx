import React, { useState, useEffect } from 'react';
import { Menu, X, ChevronDown, MapPin, Phone, Mail, Globe } from 'lucide-react';
import { Link } from 'react-router-dom';
import WhatsAppWidget from '../components/ui/WhatsAppWidget';
import { motion, AnimatePresence } from 'framer-motion';

const Landing = () => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [loginDropdownOpen, setLoginDropdownOpen] = useState(false);
  const [currentAboutIndex, setCurrentAboutIndex] = useState(0);

  // Dynamically load all images in the root images folder (excluding logos)
  const aboutFiles = import.meta.glob('/public/assets/images/*.{jpg,jpeg,png,webp}', { eager: true, query: '?url', import: 'default' });
  const aboutImages = Object.values(aboutFiles)
    .filter(path => typeof path === 'string' && !path.includes('school_logo') && !path.includes('watermark'))
    .map(path => {
      // Shift 1.jpg and 3.jpg a bit lower (revealing more of the top)
      const isShifted = path.endsWith('/1.jpg') || path.endsWith('/3.jpg');
      return { 
        src: path, 
        position: isShifted ? "object-[center_30%]" : "object-center" 
      };
    });

  // Dynamically load all images in the gallery folder
  const galleryFiles = import.meta.glob('/public/assets/images/gallery/*.{jpg,jpeg,png,webp}', { eager: true, query: '?url', import: 'default' });
  const galleryImages = Object.values(galleryFiles).filter(path => typeof path === 'string');

  useEffect(() => {
    if (aboutImages.length === 0) return;
    const timer = setInterval(() => {
      setCurrentAboutIndex((prev) => (prev + 1) % aboutImages.length);
    }, 4000); // Change image every 4 seconds
    return () => clearInterval(timer);
  }, [aboutImages.length]);

  return (
    <div className="min-h-screen bg-slate-50 font-sans text-slate-800 flex flex-col overflow-x-hidden">
      
      {/* Glassmorphic Navbar matching brand colors */}
      <nav className="fixed top-0 left-0 z-50 w-full bg-slate-50/70 backdrop-blur-lg border-b border-slate-200/50 shadow-sm transition-all duration-300">
        <div className="max-w-[1400px] mx-auto px-6 lg:px-8 flex justify-between items-center h-[90px]">
            
          {/* Logo Only */}
          <div className="flex items-center gap-4">
            <div className="w-[60px] h-[60px] bg-white rounded shadow-sm border border-slate-200 p-1 shrink-0 flex items-center justify-center">
              <img src="/assets/images/school_logo.jpg" alt="Logo" className="max-w-full max-h-full object-contain rounded-sm" onError={(e) => e.target.src='https://via.placeholder.com/48'} />
            </div>
          </div>
          
          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center gap-6 lg:gap-8">
            <Link to="/" className="text-[15px] font-medium text-slate-800 hover:text-royal-blue transition-colors">Home</Link>
            <a href="#about" className="text-[15px] font-medium text-slate-800 hover:text-royal-blue transition-colors">About Us</a>
            <a href="#gallery" className="text-[15px] font-medium text-slate-800 hover:text-royal-blue transition-colors">Gallery</a>
            <a href="#contact" className="text-[15px] font-medium text-slate-800 hover:text-royal-blue transition-colors">Contact</a>
            
            {/* Login Dropdown */}
            <div 
              className="relative group flex items-center gap-1 cursor-pointer h-[90px]"
              onMouseEnter={() => setLoginDropdownOpen(true)}
              onMouseLeave={() => setLoginDropdownOpen(false)}
            >
              <span className="text-[15px] font-medium text-slate-800 group-hover:text-royal-blue transition-colors flex items-center gap-1">
                Login <ChevronDown className="w-4 h-4" />
              </span>
              
              {/* Dropdown Menu */}
              <AnimatePresence>
                {loginDropdownOpen && (
                  <motion.div 
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 10 }}
                    className="absolute top-[70px] right-0 pt-2 z-50"
                  >
                    <div className="w-48 bg-white/95 backdrop-blur-md rounded-md shadow-lg border border-slate-100 overflow-hidden py-1">
                      <Link to="/student-login" className="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-royal-blue border-b border-slate-50">
                        Parents & Students
                      </Link>
                      <Link to="/login" className="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-royal-blue">
                        Staff Login
                      </Link>
                    </div>
                  </motion.div>
                )}
              </AnimatePresence>
            </div>

            {/* Apply Button */}
            <Link to="/apply" className="ml-2 px-6 py-2.5 bg-royal-blue text-white text-[15px] font-bold rounded hover:bg-blue-800 transition-colors shadow-sm">
              Apply
            </Link>
          </div>

          {/* Mobile Menu Toggle */}
          <button className="md:hidden p-2 text-royal-blue rounded hover:bg-slate-200" onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
            {mobileMenuOpen ? <X className="w-7 h-7" /> : <Menu className="w-7 h-7" />}
          </button>
        </div>
      </nav>

      {/* Refined Mobile Menu */}
      <AnimatePresence>
        {mobileMenuOpen && (
          <motion.div 
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: '100vh' }}
            exit={{ opacity: 0, height: 0 }}
            className="fixed inset-0 z-40 bg-white/95 backdrop-blur-xl pt-[90px] px-6 pb-6 md:hidden overflow-y-auto"
          >
            <div className="flex flex-col gap-6 text-xl font-medium text-slate-800 text-center mt-10">
              <Link to="/" onClick={() => setMobileMenuOpen(false)} className="hover:text-royal-blue">Home</Link>
              <a href="#about" onClick={() => setMobileMenuOpen(false)} className="hover:text-royal-blue">About Us</a>
              <a href="#gallery" onClick={() => setMobileMenuOpen(false)} className="hover:text-royal-blue">Gallery</a>
              <a href="#contact" onClick={() => setMobileMenuOpen(false)} className="hover:text-royal-blue">Contact</a>
              
              <div className="w-12 h-1 bg-slate-200 mx-auto my-2 rounded-full"></div>
              
              <div className="flex flex-col gap-4">
                <span className="text-sm font-bold text-slate-400 uppercase tracking-widest">Login Portals</span>
                <Link to="/student-login" onClick={() => setMobileMenuOpen(false)} className="block py-2 text-royal-blue font-bold text-lg bg-slate-50 rounded-lg">Parents & Students</Link>
                <Link to="/login" onClick={() => setMobileMenuOpen(false)} className="block py-2 text-royal-blue font-bold text-lg bg-slate-50 rounded-lg">Staff Login</Link>
              </div>
              
              <Link to="/apply" onClick={() => setMobileMenuOpen(false)} className="inline-block mt-6 w-full text-center py-4 bg-royal-blue text-white font-bold rounded-lg shadow-md text-lg">
                Apply Now
              </Link>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Main Content Area - Added pt-[90px] so content isn't hidden under fixed navbar */}
      <main className="max-w-[1000px] mx-auto px-6 py-20 pt-[130px] w-full flex-grow overflow-hidden">
        
        {/* Header Block */}
        <div id="about" className="mb-10 text-center md:text-left">
          <h4 className="text-[11px] font-bold tracking-[0.2em] text-blue-700 uppercase mb-2">
            ISMAIL AHMAD MEMORIAL ACADEMY
          </h4>
          <h5 className="text-sm font-semibold text-slate-500 mb-4 tracking-wide">
            MOTTO: KNOWLEDGE & PROGRESS
          </h5>
          <h1 className="font-serif text-4xl md:text-5xl font-bold text-blue-900 mb-6">
            About Ismail Ahmad Memorial Academy
          </h1>
          <div className="w-16 h-1 bg-royal-blue mb-12 md:ml-0 mx-auto"></div>
          
          <h2 className="text-2xl font-bold text-slate-800 mb-4">Who we are</h2>
          <p className="text-slate-600 text-base leading-relaxed text-left">
            <strong className="text-slate-800 font-semibold">Ismail Ahmad Memorial Academy</strong> is a co-educational institution committed to academic excellence, moral development, and a nurturing environment for learners in Nursery, Primary, and Junior Secondary School.
          </p>
        </div>

        {/* 1-by-1 Smooth Slideshow (About Images 1 to 4) */}
        <div className="w-full rounded-t-lg overflow-hidden bg-slate-200 relative h-[350px] md:h-[450px]">
          <AnimatePresence initial={false}>
            <motion.img
              key={currentAboutIndex}
              src={aboutImages[currentAboutIndex].src}
              alt={`About Image ${currentAboutIndex + 1}`}
              className={`absolute inset-0 w-full h-full object-cover ${aboutImages[currentAboutIndex].position}`}
              initial={{ x: '100%' }}
              animate={{ x: 0 }}
              exit={{ x: '-100%' }}
              transition={{ type: "tween", duration: 0.8, ease: "easeInOut" }}
            />
          </AnimatePresence>
        </div>
        
        {/* Our Mission & School Life */}
        <div className="py-12">
          <h3 className="text-2xl font-bold text-slate-800 mb-6">Our mission</h3>
          <ul className="list-disc pl-5 space-y-3 text-slate-600">
            <li>Deliver strong foundational academics for early years and secondary levels</li>
            <li>Build discipline, empathy, and leadership from a young age</li>
            <li>Provide modern facilities, libraries, and co-curricular activities</li>
            <li>Partner with families for every child's success</li>
          </ul>

          <h3 className="text-2xl font-bold text-slate-800 mb-6 mt-12">School life</h3>
          <p className="text-slate-600 leading-relaxed mb-8">
            Pupils and students enjoy science fairs, debate clubs, sports days, cultural weeks, and community service. 
            Our teachers combine traditional care with modern teaching methods to ensure every child thrives.
          </p>
          
          <div className="flex flex-wrap items-center gap-4">
            <Link to="/apply" className="px-6 py-2.5 bg-royal-blue text-white font-semibold rounded hover:bg-blue-800 transition-colors">
              Start Admission
            </Link>
            <a href="#contact" className="px-6 py-2.5 font-bold text-slate-700 hover:text-royal-blue transition-colors">
              Contact Office
            </a>
          </div>
        </div>

        {/* Gallery Showcase scrolling horizontally seamlessly (Gallery Images 1 to 10) */}
        <div id="gallery" className="py-16 mt-12 border-t border-slate-200 w-full overflow-hidden">
          <h2 className="font-serif text-3xl font-bold text-royal-blue mb-8 text-center">School Gallery</h2>
          
          {/* Horizontal Infinite Scrolling Container */}
          <div className="relative w-full h-[225px] overflow-hidden">
            <div className="absolute top-0 left-0 h-full flex animate-marquee hover:[animation-play-state:paused] w-max">
              
              {/* First Set */}
              {galleryImages.map((src, i) => (
                <div key={`original-${i}`} className="w-[300px] h-full shrink-0 px-2">
                  <img src={src} alt={`Gallery ${i}`} className="w-full h-full object-cover rounded-lg shadow-sm" />
                </div>
              ))}
              
              {/* Duplicate Set for Seamless Loop */}
              {galleryImages.map((src, i) => (
                <div key={`duplicate-${i}`} className="w-[300px] h-full shrink-0 px-2">
                  <img src={src} alt={`Gallery duplicate ${i}`} className="w-full h-full object-cover rounded-lg shadow-sm" />
                </div>
              ))}
              
            </div>
          </div>
        </div>

        {/* Contact Section */}
        <div id="contact" className="py-20 bg-white">
          <div className="max-w-[1200px] mx-auto px-6 lg:px-8">
            <div className="text-center mb-16">
              <h2 className="font-serif text-3xl md:text-4xl font-bold text-royal-blue mb-4">Contact Us</h2>
              <p className="text-slate-500 max-w-2xl mx-auto">We'd love to hear from you. Reach out to us for admissions, inquiries, or any other information.</p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {/* Address */}
              <div className="bg-slate-50 rounded-2xl p-8 flex flex-col items-center text-center border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div className="w-16 h-16 bg-royal-blue/10 rounded-full flex items-center justify-center text-royal-blue mb-6">
                  <MapPin className="w-8 h-8" />
                </div>
                <h3 className="text-xl font-bold text-slate-800 mb-4">Address</h3>
                <p className="text-slate-600 leading-relaxed">
                  Agency for Mass Education<br/>
                  Women Centre, Tsohon Layi<br/>
                  Jahun LGA, Jigawa State
                </p>
              </div>

              {/* Phone */}
              <div className="bg-slate-50 rounded-2xl p-8 flex flex-col items-center text-center border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div className="w-16 h-16 bg-royal-blue/10 rounded-full flex items-center justify-center text-royal-blue mb-6">
                  <Phone className="w-8 h-8" />
                </div>
                <h3 className="text-xl font-bold text-slate-800 mb-4">Phone</h3>
                <p className="text-slate-600 leading-relaxed">
                  <a href="tel:09078103435" className="hover:text-royal-blue transition-colors">0907 810 3435</a><br/>
                  <a href="tel:08036644211" className="hover:text-royal-blue transition-colors">0803 664 4211</a><br/>
                  <a href="tel:07038313471" className="hover:text-royal-blue transition-colors">0703 831 3471</a>
                </p>
              </div>

              {/* Email & Web */}
              <div className="bg-slate-50 rounded-2xl p-8 flex flex-col items-center text-center border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <div className="w-16 h-16 bg-royal-blue/10 rounded-full flex flex-col items-center justify-center text-royal-blue mb-6">
                  <Globe className="w-8 h-8" />
                </div>
                <h3 className="text-xl font-bold text-slate-800 mb-4">Online</h3>
                <p className="text-slate-600 leading-relaxed break-all">
                  <a href="mailto:ismailahmadacademic@yahoo.com" className="hover:text-royal-blue transition-colors block mb-2 font-medium">ismailahmadacademic@yahoo.com</a>
                  <a href="https://ismailahmadacademic.ng" target="_blank" rel="noreferrer" className="hover:text-royal-blue transition-colors block font-medium">www.ismailahmadacademic.ng</a>
                </p>
              </div>
            </div>
          </div>
        </div>

      </main>
      
      {/* Footer (Using School's Royal Blue) */}
      <footer className="bg-royal-blue py-16 text-white mt-auto">
        <div className="max-w-[1400px] mx-auto px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            
            {/* Left Col (Span 2) */}
            <div className="md:col-span-2 lg:pr-12">
              <h3 className="font-serif text-2xl font-bold text-white mb-2">Ismail Ahmad Memorial Academy</h3>
              <h4 className="text-blue-200 text-sm font-semibold tracking-wide mb-4">MOTTO: KNOWLEDGE & PROGRESS</h4>
              <p className="text-sm text-blue-50 leading-relaxed font-medium">
                A co-educational institution committed to academic excellence, moral development, and a nurturing environment for learners in Nursery, Primary, and Junior Secondary School.
              </p>
            </div>

            {/* Middle Col */}
            <div>
              <h4 className="font-bold text-[11px] tracking-widest uppercase mb-6 text-white">EXPLORE</h4>
              <ul className="space-y-4 text-sm text-blue-50">
                <li><Link to="/" className="hover:text-white transition-colors block">Home</Link></li>
                <li><a href="#about" className="hover:text-white transition-colors block">About Us</a></li>
                <li><Link to="/apply" className="hover:text-white transition-colors block">Admission</Link></li>
                <li><a href="#contact" className="hover:text-white transition-colors block">Contact</a></li>
              </ul>
            </div>
            
            {/* Right Col */}
            <div>
              <h4 className="font-bold text-[11px] tracking-widest uppercase mb-6 text-white">CONTACT US</h4>
              <ul className="space-y-4 text-sm text-blue-50 mb-6">
                <li className="flex items-start gap-2">
                  <span className="font-bold text-white shrink-0 mt-0.5">Phone:</span> 
                  <span className="leading-snug">0907 810 3435<br/>0803 664 4211<br/>0703 831 3471</span>
                </li>
                <li className="flex items-start gap-2 break-all">
                  <span className="font-bold text-white shrink-0 mt-0.5">Email:</span> 
                  <span>ismailahmadacademic@yahoo.com</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="font-bold text-white shrink-0 mt-0.5">Web:</span> 
                  <span>ismailahmadacademic.ng</span>
                </li>
                <li className="flex items-start gap-2">
                  <span className="font-bold text-white shrink-0 mt-0.5">Address:</span> 
                  <span className="leading-snug">Agency for Mass Education<br/>Women Centre, Tsohon Layi<br/>Jahun LGA, Jigawa State</span>
                </li>
              </ul>
              <h4 className="font-bold text-[11px] tracking-widest uppercase mb-4 text-white">PORTALS</h4>
              <ul className="space-y-3 text-sm text-blue-50">
                <li><Link to="/student-login" className="hover:text-white transition-colors block font-semibold">Parents & Students →</Link></li>
                <li><Link to="/login" className="hover:text-white transition-colors block font-semibold">Staff Login →</Link></li>
              </ul>
            </div>
          </div>
          
          <div className="text-center text-xs text-blue-200">
            <p>&copy; {new Date().getFullYear()} Ismail Ahmad Memorial Academy. All rights reserved.</p>
          </div>
        </div>
      </footer>

      {/* Floating WhatsApp Widget */}
      <WhatsAppWidget />

      {/* Global styles for the marquee animations */}
      <style dangerouslySetInnerHTML={{__html: `
        @keyframes marquee {
          0% { transform: translateX(0); }
          100% { transform: translateX(-50%); }
        }
        .animate-marquee {
          animation: marquee 80s linear infinite;
        }
      `}} />

    </div>
  );
};

export default Landing;
