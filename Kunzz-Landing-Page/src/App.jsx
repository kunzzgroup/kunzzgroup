import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';

import LanguageSync from './components/LanguageSync.jsx';
import { getDeployBasePath } from './config.js';
import { LanguageProvider } from './contexts/LanguageContext.jsx';
import AboutPage from './pages/AboutPage.jsx';
import HomePage from './pages/HomePage.jsx';
import JoinPage from './pages/JoinPage.jsx';
import HomePage_en from './pages/HomePage_en.jsx';
import AboutPage_en from './pages/AboutPage_en.jsx'
import JoinPage_en from './pages/JoinPage_en.jsx';
import AddEmployeePage from './backend/pages/AddEmployeePage.jsx';
import CostEditPage from './backend/pages/CostEditPage.jsx';
import GenerateCodePage from './backend/pages/GenerateCodePage.jsx';
import KpiEditPage from './backend/pages/KpiEditPage.jsx';
import CostDashboardPage from './backend/pages/CostDashboardPage.jsx';
import KpiDashboardPage from './backend/pages/KpiDashboardPage.jsx';
import StockListAllPage from './backend/pages/StockListAllPage.jsx';
import StockEditAllPage from './backend/pages/StockEditAllPage.jsx';
import StockRemarkPage from './backend/pages/StockRemarkPage.jsx';

function AppRoutes() {
  return (
    <>
      <LanguageSync />
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/about" element={<AboutPage />} />
        <Route path="/joinus" element={<JoinPage />} />
        <Route path="/Home_en" element={<HomePage_en />} />
        <Route path="/about_en" element={<AboutPage_en />} />
        <Route path="/join_en" element={<JoinPage_en />} />
        <Route path="/About_en" element={<Navigate to="/about_en" replace />} />
        <Route path="/Join_en" element={<Navigate to="/join_en" replace />} />
        <Route path="/backend/kpi-v2" element={<KpiDashboardPage />} />
        <Route path="/backend/cost-v2" element={<CostDashboardPage />} />
        <Route path="/backend/kpiedit-v2" element={<KpiEditPage />} />
        <Route path="/backend/costedit-v2" element={<CostEditPage />} />
        <Route path="/backend/generatecode-v2" element={<GenerateCodePage />} />
        <Route path="/backend/add-employee-v2" element={<AddEmployeePage />} />
        <Route path="/backend/stocklistall-v2" element={<StockListAllPage />} />
        <Route path="/backend/stockeditall-v2" element={<StockEditAllPage />} />
        <Route path="/backend/stockremark-v2" element={<StockRemarkPage />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </>
  );
}

export default function App() {
  const basename = typeof window !== 'undefined' ? getDeployBasePath() : '';

  return (
    <LanguageProvider>
      <BrowserRouter basename={basename}>
        <AppRoutes />
      </BrowserRouter>
    </LanguageProvider>
  );
}
