import { useEffect, useState } from 'react';

import BranchFilter from '../components/generatecode/BranchFilter.jsx';
import EditStaffModal from '../components/generatecode/EditStaffModal.jsx';
import GenerateCodeToast from '../components/generatecode/GenerateCodeToast.jsx';
import GenerateCodeToolbar, {
  DeleteConfirmModal,
  DownloadModal,
} from '../components/generatecode/GenerateCodeToolbar.jsx';
import PermissionsModal from '../components/generatecode/PermissionsModal.jsx';
import StaffTable from '../components/generatecode/StaffTable.jsx';
import BackendLayout from '../components/layout/BackendLayout.jsx';
import { useGenerateCode } from '../hooks/useGenerateCode.js';

export default function GenerateCodePage() {
  const {
    config,
    loading,
    saving,
    toast,
    searchTerm,
    setSearchTerm,
    branchL1,
    branchL2,
    changeBranchL1,
    setBranchL2,
    filteredStaff,
    editStaff,
    setEditStaff,
    deleteStaff,
    setDeleteStaff,
    permStaff,
    setPermStaff,
    downloadOpen,
    setDownloadOpen,
    downloadCompany,
    setDownloadCompany,
    showToast,
    saveEdit,
    confirmDelete,
    confirmDownload,
    savePermissions,
  } = useGenerateCode();

  const [showBackToTop, setShowBackToTop] = useState(false);

  useEffect(() => {
    const onScroll = () => setShowBackToTop(window.pageYOffset > 150);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  if (loading && !config && filteredStaff.length === 0) {
    return (
      <BackendLayout stylesheet="generatecode.css">
        <div className="container">
          <p style={{ padding: 24 }}>正在加载...</p>
        </div>
      </BackendLayout>
    );
  }

  return (
    <BackendLayout stylesheet="generatecode.css">
      <div className="container">
        <div className="header">
          <h1>{config?.pageTitle || '职员管理系统'}</h1>
        </div>

        <GenerateCodeToolbar
          searchTerm={searchTerm}
          onSearchChange={setSearchTerm}
          onClearSearch={() => setSearchTerm('')}
          onOpenDownload={() => setDownloadOpen(true)}
        />

        <div className="table-container">
          <div
            className="table-title"
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              flexWrap: 'wrap',
              gap: 10,
            }}
          >
            <span>职员列表</span>
            <BranchFilter
              branchL1={branchL1}
              branchL2={branchL2}
              onChangeL1={changeBranchL1}
              onChangeL2={setBranchL2}
            />
          </div>

          <StaffTable
            staffList={filteredStaff}
            loading={loading}
            onEdit={setEditStaff}
            onPermissions={setPermStaff}
            onDelete={setDeleteStaff}
          />
        </div>
      </div>

      <EditStaffModal
        staff={editStaff}
        open={Boolean(editStaff)}
        saving={saving}
        onClose={() => setEditStaff(null)}
        onSave={saveEdit}
        onError={(message) => showToast(message, 'error')}
      />

      <PermissionsModal
        user={permStaff}
        open={Boolean(permStaff)}
        saving={saving}
        onClose={() => setPermStaff(null)}
        onSave={savePermissions}
        onError={(message) => showToast(message, 'error')}
      />

      <DeleteConfirmModal
        staff={deleteStaff}
        open={Boolean(deleteStaff)}
        saving={saving}
        onClose={() => setDeleteStaff(null)}
        onConfirm={confirmDelete}
      />

      <DownloadModal
        open={downloadOpen}
        company={downloadCompany}
        onCompanyChange={setDownloadCompany}
        onClose={() => {
          setDownloadOpen(false);
          setDownloadCompany('');
        }}
        onConfirm={confirmDownload}
      />

      <button
        type="button"
        className={`back-to-top ${showBackToTop ? 'show' : ''}`}
        id="back-to-top-btn"
        title="回到顶部"
        onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
      >
        <i className="fas fa-chevron-up" />
      </button>

      <GenerateCodeToast toast={toast} />
    </BackendLayout>
  );
}
