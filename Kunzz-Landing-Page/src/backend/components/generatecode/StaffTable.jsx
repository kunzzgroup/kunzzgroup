import { parseBranchList } from '../../utils/generatecodeCalculations.js';

function BranchTags({ branch }) {
  const branches = parseBranchList(branch);
  if (!branches.length) {
    return <em style={{ color: '#bbb', fontSize: '0.75em' }}>无</em>;
  }
  return branches.map((item) => (
    <span className="branch-tag" key={item}>
      {item.toUpperCase()}
    </span>
  ));
}

export default function StaffTable({ staffList, loading, onEdit, onPermissions, onDelete }) {
  return (
    <div className="table-wrapper">
      <table id="codesTable">
        <thead>
          <tr>
            <th>序号</th>
            <th>所属公司</th>
            <th>职位</th>
            <th>英文姓名</th>
            <th>邮箱</th>
            <th>联络号码</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          {loading ? (
            <tr>
              <td colSpan={7} style={{ textAlign: 'center', padding: 30 }}>
                <div className="loading" />
                正在加载数据...
              </td>
            </tr>
          ) : null}

          {!loading && staffList.length === 0 ? (
            <tr>
              <td colSpan={7} style={{ textAlign: 'center', padding: 30, color: '#666' }}>
                📝 暂无数据
              </td>
            </tr>
          ) : null}

          {!loading
            ? staffList.map((staff, index) => (
                <tr key={staff.id} id={`row-${staff.id}`} data-id={staff.id} data-branch={staff.branch || ''}>
                  <td style={{ textAlign: 'center', fontWeight: 'bold', color: 'black' }}>{index + 1}</td>
                  <td style={{ textAlign: 'center' }}>
                    <BranchTags branch={staff.branch} />
                  </td>
                  <td>
                    <div style={{ fontWeight: 700, color: '#333' }}>{staff.position || '-'}</div>
                  </td>
                  <td>
                    <div style={{ fontWeight: 500 }}>{staff.username || <em style={{ color: '#999' }}>-</em>}</div>
                  </td>
                  <td>{staff.email || <em style={{ color: '#999' }}>-</em>}</td>
                  <td>{staff.phone_number || <em style={{ color: '#999' }}>-</em>}</td>
                  <td>
                    <div className="action-buttons">
                      <button
                        type="button"
                        className="btn-action btn-edit"
                        title="编辑"
                        onClick={() => onEdit(staff)}
                      >
                        <i className="fas fa-edit" />
                      </button>
                      <button
                        type="button"
                        className="btn-action btn-save"
                        title="权限设定"
                        style={{ background: '#ff8019' }}
                        onClick={() => onPermissions(staff)}
                      >
                        <i className="fas fa-user-shield" />
                      </button>
                      <button
                        type="button"
                        className="btn-action btn-delete"
                        onClick={() => onDelete(staff)}
                      >
                        <i className="fas fa-trash" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            : null}
        </tbody>
      </table>
    </div>
  );
}
