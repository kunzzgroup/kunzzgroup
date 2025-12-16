import React, { useState, useMemo } from 'react';
import { Calendar, Users, DollarSign, Table, TrendingUp, Clock, Plus, Edit, Trash2, Save, X, Search, Download, Settings, BarChart3 } from 'lucide-react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';

const IntegratedRestaurantApp = () => {
  // 主数据状态 - 包含实际录入的数据
  const [actualData, setActualData] = useState([
    {
      id: 1,
      date: '2024-07-29',
      grossSales: 15000,
      costs: 3750,
      discounts: 750,
      diners: 120,
      tablesUsed: 18
    },
    {
      id: 2,
      date: '2024-07-28',
      grossSales: 18000,
      costs: 4500,
      discounts: 900,
      diners: 145,
      tablesUsed: 20
    }
  ]);

  // 应用状态
  const [currentView, setCurrentView] = useState('dashboard'); // 'dashboard' 或 'admin'
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [searchDate, setSearchDate] = useState('');
  
  // KPI仪表板的日期范围状态
  const [dateRange, setDateRange] = useState({
    startDate: '2024-07-01',
    endDate: '2024-07-31'
  });
  const [selectedMonth, setSelectedMonth] = useState('2024-07');

  // 表单数据状态
  const [formData, setFormData] = useState({
    date: '',
    grossSales: '',
    costs: '',
    discounts: '',
    diners: '',
    tablesUsed: ''
  });

  // 将实际数据转换为KPI仪表板需要的格式
  const convertToKPIFormat = (data) => {
    return data.map(item => {
      const netSales = item.grossSales - item.costs - item.discounts;
      return {
        date: item.date,
        totalSales: item.grossSales,
        netSales: netSales,
        diners: item.diners,
        tablesUsed: item.tablesUsed,
        avgSalesPerDiner: Math.round(item.grossSales / item.diners),
        avgSalesPerTable: Math.round(item.grossSales / item.tablesUsed),
        peakHour: 12 + Math.floor(Math.random() * 3), // 模拟数据
        satisfactionScore: 4.0 + Math.random() * 1.0 // 模拟数据
      };
    });
  };

  // 为KPI仪表板准备数据
  const kpiData = useMemo(() => convertToKPIFormat(actualData), [actualData]);

  // KPI仪表板相关函数
  const handleMonthChange = (month) => {
    setSelectedMonth(month);
    const year = month.split('-')[0];
    const monthNum = month.split('-')[1];
    const firstDay = `${year}-${monthNum}-01`;
    const lastDay = new Date(year, monthNum, 0).getDate();
    const lastDayFormatted = `${year}-${monthNum}-${lastDay.toString().padStart(2, '0')}`;
    
    setDateRange({
      startDate: firstDay,
      endDate: lastDayFormatted
    });
  };

  const filteredKPIData = useMemo(() => {
    return kpiData.filter(item => {
      const itemDate = new Date(item.date);
      const start = new Date(dateRange.startDate);
      const end = new Date(dateRange.endDate);
      return itemDate >= start && itemDate <= end;
    });
  }, [kpiData, dateRange]);

  const summary = useMemo(() => {
    if (filteredKPIData.length === 0) return {};
    
    const totalSales = filteredKPIData.reduce((sum, item) => sum + item.totalSales, 0);
    const netSales = filteredKPIData.reduce((sum, item) => sum + item.netSales, 0);
    const totalDiners = filteredKPIData.reduce((sum, item) => sum + item.diners, 0);
    const totalTables = filteredKPIData.reduce((sum, item) => sum + item.tablesUsed, 0);
    
    return {
      totalSales,
      netSales,
      totalDiners,
      totalTables,
      avgSalesPerDiner: Math.round(totalSales / totalDiners),
      days: filteredKPIData.length
    };
  }, [filteredKPIData]);

  // 后台管理相关函数
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = () => {
    if (!formData.date || !formData.grossSales || !formData.diners) {
      alert('请填写必填字段：日期、总销售额、用餐人数');
      return;
    }

    const newItem = {
      id: editingItem ? editingItem.id : Date.now(),
      date: formData.date,
      grossSales: parseFloat(formData.grossSales) || 0,
      costs: parseFloat(formData.costs) || 0,
      discounts: parseFloat(formData.discounts) || 0,
      diners: parseInt(formData.diners) || 0,
      tablesUsed: parseInt(formData.tablesUsed) || 0
    };

    if (editingItem) {
      setActualData(prev => prev.map(item => item.id === editingItem.id ? newItem : item));
    } else {
      setActualData(prev => [...prev, newItem]);
    }

    resetForm();
  };

  const resetForm = () => {
    setFormData({
      date: '',
      grossSales: '',
      costs: '',
      discounts: '',
      diners: '',
      tablesUsed: ''
    });
    setEditingItem(null);
    setIsModalOpen(false);
  };

  const handleEdit = (item) => {
    setFormData({
      date: item.date,
      grossSales: item.grossSales.toString(),
      costs: item.costs.toString(),
      discounts: item.discounts.toString(),
      diners: item.diners.toString(),
      tablesUsed: item.tablesUsed.toString()
    });
    setEditingItem(item);
    setIsModalOpen(true);
  };

  const handleDelete = (id) => {
    if (window.confirm('确定要删除这条记录吗？')) {
      setActualData(prev => prev.filter(item => item.id !== id));
    }
  };

  const filteredAdminData = actualData.filter(item => 
    searchDate ? item.date.includes(searchDate) : true
  ).sort((a, b) => new Date(b.date) - new Date(a.date));

  const calculateNetSales = (gross, costs, discounts) => {
    return gross - costs - discounts;
  };

  // 渲染KPI仪表板
  const renderDashboard = () => (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* 标题和切换按钮 */}
        <div className="mb-8 flex justify-between items-center">
          <h1 className="text-3xl font-bold text-gray-900">公司餐厅KPI仪表板</h1>
          <button
            onClick={() => setCurrentView('admin')}
            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors"
          >
            <Settings className="w-4 h-4" />
            数据管理
          </button>
        </div>
        
        {/* 日期选择器 */}
        <div className="bg-white p-6 rounded-lg shadow-sm border mb-8">
          <div className="flex flex-wrap gap-4 items-center">
            <div className="flex items-center gap-2">
              <Calendar className="w-5 h-5 text-gray-500" />
              <label className="text-sm font-medium text-gray-700">选择月份:</label>
              <input
                type="month"
                value={selectedMonth}
                onChange={(e) => handleMonthChange(e.target.value)}
                className="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div className="w-px h-6 bg-gray-300"></div>

            <div className="flex items-center gap-2">
              <label className="text-sm font-medium text-gray-700">开始日期:</label>
              <input
                type="date"
                value={dateRange.startDate}
                onChange={(e) => setDateRange(prev => ({ ...prev, startDate: e.target.value }))}
                className="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            
            <div className="flex items-center gap-2">
              <label className="text-sm font-medium text-gray-700">结束日期:</label>
              <input
                type="date"
                value={dateRange.endDate}
                onChange={(e) => setDateRange(prev => ({ ...prev, endDate: e.target.value }))}
                className="border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            
            <div className="text-sm text-gray-600">
              已选择 {summary.days} 天的数据
            </div>
          </div>
        </div>

        {/* KPI 概览卡片 */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">总销售额</p>
                <p className="text-2xl font-bold text-gray-900">¥{summary.totalSales?.toLocaleString()}</p>
              </div>
              <DollarSign className="w-8 h-8 text-green-500" />
            </div>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">净销售额</p>
                <p className="text-2xl font-bold text-gray-900">¥{summary.netSales?.toLocaleString()}</p>
              </div>
              <TrendingUp className="w-8 h-8 text-emerald-500" />
            </div>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">桌子总数</p>
                <p className="text-2xl font-bold text-gray-900">{summary.totalTables?.toLocaleString()}</p>
              </div>
              <Table className="w-8 h-8 text-purple-500" />
            </div>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">人数总数</p>
                <p className="text-2xl font-bold text-gray-900">{summary.totalDiners?.toLocaleString()}</p>
              </div>
              <Users className="w-8 h-8 text-blue-500" />
            </div>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">人均消费</p>
                <p className="text-2xl font-bold text-gray-900">¥{summary.avgSalesPerDiner}</p>
              </div>
              <Clock className="w-8 h-8 text-orange-500" />
            </div>
          </div>
        </div>

        {/* 图表区域 */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">销售趋势</h3>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={filteredKPIData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis 
                  dataKey="date" 
                  tick={{ fontSize: 12 }}
                  tickFormatter={(value) => new Date(value).getDate().toString()}
                />
                <YAxis tick={{ fontSize: 12 }} />
                <Tooltip 
                  labelFormatter={(value) => `日期: ${value}`}
                  formatter={(value, name) => [`¥${value.toLocaleString()}`, '销售额']}
                />
                <Line type="monotone" dataKey="totalSales" stroke="#3B82F6" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>

          <div className="bg-white p-6 rounded-lg shadow-sm border">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">用餐人数趋势</h3>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={filteredKPIData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis 
                  dataKey="date" 
                  tick={{ fontSize: 12 }}
                  tickFormatter={(value) => new Date(value).getDate().toString()}
                />
                <YAxis tick={{ fontSize: 12 }} />
                <Tooltip 
                  labelFormatter={(value) => `日期: ${value}`}
                  formatter={(value, name) => [`${value}人`, '用餐人数']}
                />
                <Bar dataKey="diners" fill="#10B981" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* 详细数据表格 */}
        <div className="bg-white rounded-lg shadow-sm border">
          <div className="p-6 border-b">
            <h3 className="text-lg font-semibold text-gray-900">详细数据</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日期</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">总销售额</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">净销售额</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用餐人数</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">使用桌数</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">人均消费</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredKPIData.slice(-10).map((item, index) => (
                  <tr key={index} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{item.date}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥{item.totalSales.toLocaleString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥{item.netSales.toLocaleString()}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{item.diners}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{item.tablesUsed}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥{item.avgSalesPerDiner}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );

  // 渲染后台管理界面
  const renderAdmin = () => (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        <div className="mb-8 flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">餐厅数据管理后台</h1>
            <p className="text-gray-600">管理餐厅日常运营数据，包括销售额、成本、折扣等信息</p>
          </div>
          <button
            onClick={() => setCurrentView('dashboard')}
            className="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2 transition-colors"
          >
            <BarChart3 className="w-4 h-4" />
            查看仪表板
          </button>
        </div>

        {/* 操作栏 */}
        <div className="bg-white p-6 rounded-lg shadow-sm border mb-6">
          <div className="flex flex-wrap gap-4 items-center justify-between">
            <div className="flex gap-4 items-center">
              <button
                onClick={() => setIsModalOpen(true)}
                className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors"
              >
                <Plus className="w-4 h-4" />
                添加新记录
              </button>
            </div>

            <div className="flex items-center gap-2">
              <Search className="w-5 h-5 text-gray-400" />
              <input
                type="date"
                value={searchDate}
                onChange={(e) => setSearchDate(e.target.value)}
                placeholder="搜索日期"
                className="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              {searchDate && (
                <button
                  onClick={() => setSearchDate('')}
                  className="text-gray-500 hover:text-gray-700"
                >
                  <X className="w-4 h-4" />
                </button>
              )}
            </div>
          </div>
        </div>

        {/* 数据表格 */}
        <div className="bg-white rounded-lg shadow-sm border overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">日期</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">总销售额</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">成本</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">折扣</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">净销售额</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用餐人数</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">使用桌数</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">人均消费</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredAdminData.map((item) => {
                  const netSales = calculateNetSales(item.grossSales, item.costs, item.discounts);
                  const avgPerDiner = item.diners > 0 ? Math.round(item.grossSales / item.diners) : 0;
                  
                  return (
                    <tr key={item.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{item.date}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥{item.grossSales.toLocaleString()}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-red-600">¥{item.costs.toLocaleString()}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-orange-600">¥{item.discounts.toLocaleString()}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">¥{netSales.toLocaleString()}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{item.diners}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{item.tablesUsed}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥{avgPerDiner}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div className="flex gap-2">
                          <button
                            onClick={() => handleEdit(item)}
                            className="text-blue-600 hover:text-blue-800 transition-colors"
                          >
                            <Edit className="w-4 h-4" />
                          </button>
                          <button
                            onClick={() => handleDelete(item.id)}
                            className="text-red-600 hover:text-red-800 transition-colors"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          
          {filteredAdminData.length === 0 && (
            <div className="text-center py-12">
              <p className="text-gray-500">暂无数据</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );

  return (
    <div>
      {currentView === 'dashboard' ? renderDashboard() : renderAdmin()}
      
      {/* 添加/编辑模态框 */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6 border-b">
              <h2 className="text-xl font-semibold text-gray-900">
                {editingItem ? '编辑记录' : '添加新记录'}
              </h2>
            </div>
            
            <div className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    日期 <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="date"
                    name="date"
                    value={formData.date}
                    onChange={handleInputChange}
                    required
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    总销售额 (¥) <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    name="grossSales"
                    value={formData.grossSales}
                    onChange={handleInputChange}
                    required
                    min="0"
                    step="0.01"
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    成本 (¥)
                  </label>
                  <input
                    type="number"
                    name="costs"
                    value={formData.costs}
                    onChange={handleInputChange}
                    min="0"
                    step="0.01"
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    折扣 (¥)
                  </label>
                  <input
                    type="number"
                    name="discounts"
                    value={formData.discounts}
                    onChange={handleInputChange}
                    min="0"
                    step="0.01"
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    用餐人数 <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="number"
                    name="diners"
                    value={formData.diners}
                    onChange={handleInputChange}
                    required
                    min="0"
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    使用桌数
                  </label>
                  <input
                    type="number"
                    name="tablesUsed"
                    value={formData.tablesUsed}
                    onChange={handleInputChange}
                    min="0"
                    max="50"
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <div className="flex justify-end gap-4 mt-6 pt-6 border-t">
                <button
                  type="button"
                  onClick={resetForm}
                  className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition-colors"
                >
                  <X className="w-4 h-4" />
                  取消
                </button>
                <button
                  type="button"
                  onClick={handleSubmit}
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors"
                >
                  <Save className="w-4 h-4" />
                  保存
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default IntegratedRestaurantApp;