import React, { useState } from 'react';
import axios from 'axios';

/**
 * مكون الرفع الذكي (Smart Upload Component)
 * 
 * يتيح للباحثين رفع أبحاثهم، يتصل بـ Laravel API، 
 * ويعرض حالة المعالجة (رفع -> تحليل ذكاء اصطناعي -> نجاح)
 */
const SmartUpload = () => {
    // حالات النظام: idle, pending (رفع), processing (ذكاء اصطناعي), success, error
    const [status, setStatus] = useState('idle');
    const [progress, setProgress] = useState(0);
    const [title, setTitle] = useState('');
    const [file, setFile] = useState(null);
    const [message, setMessage] = useState('');

    // التقاط الملف
    const handleFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            const selectedFile = e.target.files[0];
            if (selectedFile.type !== 'application/pdf') {
                setStatus('error');
                setMessage('يرجى اختيار ملف PDF حصراً.');
                return;
            }
            setFile(selectedFile);
            setStatus('idle');
            setMessage('');
        }
    };

    // دالة الاتصال بالباك إند (Axios)
    const handleUpload = async (e) => {
        e.preventDefault();
        
        if (!file || !title) {
            setStatus('error');
            setMessage('يرجى إدخال عنوان البحث واختيار ملف.');
            return;
        }

        const formData = new FormData();
        formData.append('title', title);
        formData.append('file', file);

        try {
            setStatus('pending');
            setMessage('جاري نقل الملف إلى الخادم الأكاديمي...');

            // الاتصال بمسار الرفع الذي تم بناءه في Laravel
            const response = await axios.post('http://localhost:8000/api/v1/thesis/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json',
                    // ملاحظة: يجب تمرير التوكن الحقيقي للمستخدم هنا
                    // 'Authorization': `Bearer ${localStorage.getItem('token')}` 
                },
                // مراقبة نسبة الرفع لشريط التقدم
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    setProgress(percentCompleted);
                }
            });

            // لارافيل يرد بكود 202 (Accepted) لأن المهمة أُرسلت للطابور
            if (response.status === 202) {
                setStatus('processing');
                setMessage('تم الاستلام! جاري استخراج البيانات بالذكاء الاصطناعي...');
                
                // هنا في نظام الإنتاج نقوم بعمل Polling (سؤال السيرفر كل 3 ثوان) 
                // أو نستخدم WebSockets/Pusher لمعرفة متى تنتهي عملية الـ AI.
                // هذه محاكاة للانتظار لتوضيح تجربة المستخدم:
                simulateAIProcessing();
            }

        } catch (error) {
            setStatus('error');
            setMessage(error.response?.data?.message || 'حدث خطأ غير متوقع أثناء الاتصال بالخادم.');
        }
    };

    // محاكاة مؤقتة لعمل الطابور (Queues)
    const simulateAIProcessing = () => {
        setTimeout(() => {
            setStatus('success');
            setMessage('تمت الأرشفة بنجاح واستخراج الكلمات المفتاحية!');
            setTitle('');
            setFile(null);
        }, 5000); // 5 ثواني تقريباً
    };

    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans dir-rtl">
            <div className="w-full max-w-lg bg-white rounded-2xl shadow-xl shadow-slate-200/50 p-8 border border-slate-100 transition-all">
                
                <div className="text-center mb-8">
                    <h2 className="text-2xl font-bold text-slate-800 tracking-tight">الرفع الأكاديمي الذكي</h2>
                    <p className="text-slate-500 text-sm mt-2">نظام EduTrack يقرأ بحثك آلياً ليقوم بأرشفته</p>
                </div>

                <form onSubmit={handleUpload} className="space-y-6">
                    {/* حقل عنوان البحث */}
                    <div>
                        <label className="block text-sm font-semibold text-slate-700 mb-2">عنوان البحث</label>
                        <input 
                            type="text" 
                            value={title}
                            onChange={(e) => setTitle(e.target.value)}
                            disabled={status === 'pending' || status === 'processing'}
                            className="w-full px-4 py-3 rounded-lg border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-slate-700 bg-slate-50/50"
                            placeholder="أدخل عنوان رسالتك العلمية..."
                        />
                    </div>

                    {/* منطقة السحب والإفلات (Drag & Drop) */}
                    <div className="relative group">
                        <div className={`flex justify-center items-center w-full px-6 py-10 border-2 border-dashed rounded-xl transition-all 
                            ${file ? 'border-emerald-400 bg-emerald-50' : 'border-slate-300 hover:border-blue-400 hover:bg-blue-50/30'}
                            ${(status === 'pending' || status === 'processing') && 'opacity-50 pointer-events-none'}`}>
                            
                            <div className="text-center">
                                <svg className={`mx-auto h-12 w-12 mb-3 ${file ? 'text-emerald-500' : 'text-slate-400 group-hover:text-blue-500'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                
                                <span className="mt-2 block text-sm font-medium text-slate-600">
                                    {file ? file.name : 'اسحب ملف الـ PDF هنا أو انقر للاختيار'}
                                </span>
                            </div>
                            <input 
                                type="file" 
                                accept=".pdf" 
                                onChange={handleFileChange} 
                                disabled={status === 'pending' || status === 'processing'}
                                className="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                            />
                        </div>
                    </div>

                    {/* رسائل التنبيه */}
                    {message && (
                        <div className={`p-4 rounded-lg text-sm font-medium flex items-center gap-3
                            ${status === 'error' ? 'bg-red-50 text-red-600' : ''}
                            ${status === 'success' ? 'bg-emerald-50 text-emerald-700' : ''}
                            ${status === 'processing' ? 'bg-blue-50 text-blue-700' : ''}
                            ${status === 'pending' ? 'bg-slate-100 text-slate-700' : ''}
                        `}>
                            {status === 'processing' && <span className="animate-spin h-4 w-4 border-2 border-blue-600 border-t-transparent rounded-full"></span>}
                            {message}
                        </div>
                    )}

                    {/* شريط التقدم (Progress Bar) */}
                    {status === 'pending' && (
                        <div className="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                            <div 
                                className="bg-blue-600 h-2.5 rounded-full transition-all duration-300 ease-out"
                                style={{ width: `${progress}%` }}
                            ></div>
                        </div>
                    )}

                    {/* زر الإرسال */}
                    <button 
                        type="submit"
                        disabled={status === 'pending' || status === 'processing'}
                        className={`w-full py-3.5 px-4 rounded-xl text-white font-semibold shadow-md transition-all
                            ${status === 'success' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-200'}
                            ${(status === 'pending' || status === 'processing') ? 'opacity-70 cursor-not-allowed' : 'hover:-translate-y-0.5'}
                        `}
                    >
                        {status === 'success' ? 'رفع بحث آخر' : 
                         status === 'processing' ? 'الذكاء الاصطناعي يعمل...' : 
                         status === 'pending' ? `جاري الرفع ${progress}%` : 'تأكيد ورفع البحث'}
                    </button>
                </form>
            </div>
        </div>
    );
};

export default SmartUpload;
