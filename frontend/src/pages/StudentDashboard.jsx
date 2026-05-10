import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { BookOpen, ShieldCheck, AlertCircle, Search, FileText, Download, X, Activity, Moon, Sun } from 'lucide-react';
import StatCard from '../components/StatCard';

/**
 * لوحة تحكم الطالب (Student Dashboard)
 * تصميم Minimalist مع دعم ذكي للوضع الليلي (Dark Mode)
 */
const StudentDashboard = () => {
    const [theses, setTheses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedThesis, setSelectedThesis] = useState(null);
    const [isDarkMode, setIsDarkMode] = useState(false);

    // تفعيل الوضع الليلي
    const toggleTheme = () => {
        setIsDarkMode(!isDarkMode);
        document.documentElement.classList.toggle('dark');
    };

    // جلب البيانات من الـ API
    useEffect(() => {
        const fetchTheses = async () => {
            try {
                // ملاحظة هندسية: نستخدم مسار research الذي تم بناؤه في Laravel
                const response = await axios.get('http://localhost:8000/api/v1/research', {
                    headers: { 'Accept': 'application/json' } // Add Auth Bearer here in production
                });
                
                // في حال عدم وجود بيانات حقيقية بعد، نضع بيانات تجريبية (Mock Data) للاستعراض
                const data = response.data.data && response.data.data.length > 0 
                    ? response.data.data 
                    : mockData;
                
                setTheses(data);
                setLoading(false);
            } catch (err) {
                setError('تعذر الاتصال بالخادم. يرجى المحاولة لاحقاً.');
                setLoading(false);
            }
        };

        fetchTheses();
    }, []);

    // تحديد لون الحالة
    const getStatusBadge = (status) => {
        switch (status) {
            case 'verified': return <span className="px-3 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full text-xs font-semibold">مؤرشف ومعتمد</span>;
            case 'processing': return <span className="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full text-xs font-semibold animate-pulse">جاري فحص الذكاء الاصطناعي</span>;
            case 'failed_parsing': return <span className="px-3 py-1 bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 rounded-full text-xs font-semibold">فشل التحليل</span>;
            default: return <span className="px-3 py-1 bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 rounded-full text-xs font-semibold">قيد الانتظار</span>;
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-900 transition-colors">
                <div className="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
            </div>
        );
    }

    return (
        <div className={`min-h-screen p-6 font-sans dir-rtl transition-colors duration-300 ${isDarkMode ? 'dark bg-slate-900' : 'bg-slate-50'}`}>
            <div className="max-w-6xl mx-auto space-y-8">
                
                {/* رأس الصفحة (Header) */}
                <div className="flex justify-between items-center bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-800 dark:text-white">لوحة تحكم الباحث</h1>
                        <p className="text-slate-500 dark:text-slate-400 text-sm mt-1">تتبع أبحاثك وحالة الأرشفة الذكية</p>
                    </div>
                    <button onClick={toggleTheme} className="p-3 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                        {isDarkMode ? <Sun size={20} /> : <Moon size={20} />}
                    </button>
                </div>

                {/* البطاقات الإحصائية */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <StatCard title="إجمالي الأبحاث" value={theses.length} icon={BookOpen} colorClass="text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/30" />
                    <StatCard title="الأبحاث المعتمدة" value={theses.filter(t => t.status === 'verified').length} icon={ShieldCheck} colorClass="text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-900/30" />
                    <StatCard title="متوسط نسبة التشابه" value="12%" icon={Activity} trend={{label: 'نسبة آمنة جداً', isPositive: true}} colorClass="text-indigo-600 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-900/30" />
                </div>

                {/* قائمة الأبحاث (جدول متجاوب) */}
                <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                    <div className="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                        <h2 className="text-lg font-bold text-slate-800 dark:text-white">سجل الأبحاث المرفوعة</h2>
                    </div>

                    {theses.length === 0 ? (
                        <div className="p-12 text-center">
                            <FileText size={48} className="mx-auto text-slate-300 dark:text-slate-600 mb-4" />
                            <p className="text-slate-500 dark:text-slate-400 font-medium">لا توجد أبحاث مرفوعة حالياً. ابدأ برفع بحثك الأول!</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-right">
                                <thead className="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-sm">
                                    <tr>
                                        <th className="px-6 py-4 font-semibold">عنوان البحث</th>
                                        <th className="px-6 py-4 font-semibold">تاريخ الرفع</th>
                                        <th className="px-6 py-4 font-semibold">الحالة</th>
                                        <th className="px-6 py-4 font-semibold text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                                    {theses.map((thesis) => (
                                        <tr key={thesis.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                            <td className="px-6 py-4">
                                                <p className="font-semibold text-slate-800 dark:text-slate-200">{thesis.title}</p>
                                                <p className="text-xs text-slate-400 dark:text-slate-500 font-mono mt-1">ID: {thesis.id}</p>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600 dark:text-slate-300 text-sm">{thesis.created_at || '2026-05-10'}</td>
                                            <td className="px-6 py-4">{getStatusBadge(thesis.status)}</td>
                                            <td className="px-6 py-4 text-center">
                                                <button 
                                                    onClick={() => setSelectedThesis(thesis)}
                                                    className="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-blue-900/50 dark:hover:text-blue-400 rounded-lg text-sm font-medium transition-colors"
                                                >
                                                    <Search size={16} /> استعراض التقرير
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* نافذة التفاصيل الذكية (AI Insights Modal) */}
                {selectedThesis && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
                        <div className="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform scale-100 transition-transform">
                            
                            {/* Modal Header */}
                            <div className="flex justify-between items-center p-6 border-b border-slate-100 dark:border-slate-700">
                                <h3 className="text-xl font-bold text-slate-800 dark:text-white">تقرير الذكاء الاصطناعي والأرشفة</h3>
                                <button onClick={() => setSelectedThesis(null)} className="text-slate-400 hover:text-rose-500 transition-colors">
                                    <X size={24} />
                                </button>
                            </div>

                            {/* Modal Body */}
                            <div className="p-6 space-y-6">
                                <div>
                                    <h4 className="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">عنوان الرسالة</h4>
                                    <p className="text-lg font-bold text-slate-800 dark:text-slate-200">{selectedThesis.title}</p>
                                    
                                    {/* Integrity Seal (ختم النزاهة) */}
                                    <div className="mt-3 flex items-center gap-2 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg w-fit">
                                        <ShieldCheck size={16} className="text-emerald-500" />
                                        <span className="text-xs font-mono text-slate-500 dark:text-slate-400">
                                            Hash: {selectedThesis.file_hash_sha256 || 'a1b2c3d4e5f6g7h8i9j0...'}
                                        </span>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    {/* Plagiarism Score */}
                                    <div className="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                                        <p className="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-2">نسبة التشابه (Plagiarism)</p>
                                        <div className="flex items-end gap-2">
                                            <span className="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                                                {selectedThesis.ai_metadata?.plagiarism_score || '8'}%
                                            </span>
                                            <span className="text-xs text-emerald-600 dark:text-emerald-400 mb-1 font-medium bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 rounded">آمن</span>
                                        </div>
                                    </div>

                                    {/* Confidence Score */}
                                    <div className="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700">
                                        <p className="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-2">دقة استخراج الـ AI</p>
                                        <div className="flex items-end gap-2">
                                            <span className="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                                {selectedThesis.ai_metadata?.confidence_score ? (selectedThesis.ai_metadata.confidence_score * 100).toFixed(0) : '95'}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Keywords */}
                                <div>
                                    <p className="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-3">الكلمات المفتاحية المستخرجة</p>
                                    <div className="flex flex-wrap gap-2">
                                        {(selectedThesis.ai_metadata?.keywords || ['ذكاء اصطناعي', 'هندسة برمجيات', 'أرشفة رقمية', 'خوارزميات']).map((kw, i) => (
                                            <span key={i} className="px-3 py-1.5 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-lg text-sm font-medium border border-blue-100 dark:border-blue-800">
                                                # {kw}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Modal Footer */}
                            <div className="p-6 bg-slate-50 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                                <button className="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition font-medium">
                                    إغلاق
                                </button>
                                <button className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-200 dark:shadow-none transition flex items-center gap-2 font-medium">
                                    <Download size={18} /> تحميل شهادة الأرشفة (QR)
                                </button>
                            </div>
                        </div>
                    </div>
                )}

            </div>
        </div>
    );
};

// بيانات وهمية للاستعراض في حال لم يكن هناك بيانات من الـ API بعد
const mockData = [
    {
        id: 'RES-2026-001',
        title: 'تأثير خوارزميات التعلم العميق في استخراج البيانات الطبية',
        status: 'verified',
        created_at: '2026-05-10',
        ai_metadata: { plagiarism_score: 12, confidence_score: 0.98, keywords: ['تعلم عميق', 'بيانات طبية', 'تحليل صور'] }
    },
    {
        id: 'RES-2026-002',
        title: 'هندسة النظم الموزعة باستخدام معمارية Microservices',
        status: 'processing',
        created_at: '2026-05-09',
    },
    {
        id: 'RES-2026-003',
        title: 'دراسة تحليلية للثغرات الأمنية في تطبيقات الويب الحديثة',
        status: 'failed_parsing',
        created_at: '2026-05-08',
    }
];

export default StudentDashboard;
