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
import StockProductNamePage from './backend/pages/StockProductNamePage.jsx';
import StockSotPage from './backend/pages/StockSotPage.jsx';
import StockMinimumPage from './backend/pages/StockMinimumPage.jsx';
import DishwareStockPage from './backend/pages/DishwareStockPage.jsx';
import PricePage from './backend/pages/PricePage.jsx';
import SupplyPage from './backend/pages/SupplyPage.jsx';
import BgMusicUploadPage from './backend/pages/BgMusicUploadPage.jsx';

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
        <Route path="/backend/stockproductname-v2" element={<StockProductNamePage />} />
        <Route path="/backend/stocksot-v2" element={<StockSotPage />} />
        <Route path="/backend/stockminimum-v2" element={<StockMinimumPage />} />
        <Route path="/backend/dishware_stock-v2" element={<DishwareStockPage />} />
        <Route path="/backend/price-v2" element={<PricePage />} />
        <Route path="/backend/supply-v2" element={<SupplyPage />} />
        <Route path="/backend/bgmusicupload-v2" element={<BgMusicUploadPage />} />
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
