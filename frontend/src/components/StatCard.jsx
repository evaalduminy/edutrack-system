import React from 'react';

/**
 * مكون البطاقة الإحصائية (StatCard)
 * يدعم الوضع الليلي (Dark Mode) بأسلوب معدني هادئ
 */
const StatCard = ({ title, value, icon: Icon, trend, colorClass = "text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/30" }) => {
    return (
        <div className="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm hover:shadow-lg border border-slate-100 dark:border-slate-700 transition-all duration-300">
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">{title}</p>
                    <h3 className="text-3xl font-bold text-slate-800 dark:text-slate-100">{value}</h3>
                    {trend && (
                        <p className={`text-xs mt-2 font-medium flex items-center gap-1 ${trend.isPositive ? 'text-emerald-500' : 'text-amber-500'}`}>
                            {trend.label}
                        </p>
                    )}
                </div>
                <div className={`p-4 rounded-xl ${colorClass}`}>
                    <Icon size={28} strokeWidth={1.5} />
                </div>
            </div>
        </div>
    );
};

export default StatCard;
