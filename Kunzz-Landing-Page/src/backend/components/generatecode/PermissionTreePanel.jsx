export default function PermissionTreePanel({ onCloseDetail }) {
  return (
    <div className="perm-layout-container">
      <div className="perm-tree-container">
        <div className="perm-level-1">
          <div className="perm-level-1-item" data-perm="brand">
            <label className="perm-checkbox-label">
              <input type="checkbox" className="perm-l1-check" value="brand" />
              <span className="perm-arrow">▶</span>
              <strong>集团架构</strong>
            </label>
          </div>
          <div className="perm-level-2-container" data-parent="brand">
            <div className="perm-level-2-item has-level-3" data-sub="kunzz_holdings">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="brand" value="kunzz_holdings" />
                <span className="perm-arrow-sub">▶</span>
                <span>KUNZZ HOLDINGS SDN BHD</span>
              </label>
            </div>
            <div className="perm-level-2-item has-level-3" data-sub="tokyo_cuisine">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="brand" value="tokyo_cuisine" />
                <span className="perm-arrow-sub">▶</span>
                <span>TOKYO JAPANESE CUISINE SDN BHD</span>
              </label>
            </div>
            <div className="perm-level-2-item has-level-3" data-sub="tokyo_izakaya">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="brand" value="tokyo_izakaya" />
                <span className="perm-arrow-sub">▶</span>
                <span>TOKYO IZAKAYA SDN BHD</span>
              </label>
            </div>
          </div>
        </div>

        <div className="perm-level-1">
          <div className="perm-level-1-item" data-perm="analytics">
            <label className="perm-checkbox-label">
              <input type="checkbox" className="perm-l1-check" value="analytics" />
              <span className="perm-arrow">▶</span>
              <strong>营收数据</strong>
            </label>
          </div>
          <div className="perm-level-2-container" data-parent="analytics">
            <div className="perm-level-2-item">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="analytics" value="kpi_report" />
                <span>KPI报表</span>
              </label>
            </div>
            <div className="perm-level-2-item has-level-3" data-sub="kpi_upload">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="analytics" value="kpi_upload" />
                <span className="perm-arrow-sub">▶</span>
                <span>数据上传</span>
              </label>
            </div>
          </div>
        </div>

        <div className="perm-level-1">
          <div className="perm-level-1-item" data-perm="hr">
            <label className="perm-checkbox-label">
              <input type="checkbox" className="perm-l1-check" value="hr" />
              <span className="perm-arrow">▶</span>
              <strong>人事管理</strong>
            </label>
          </div>
          <div className="perm-level-2-container" data-parent="hr">
            <div className="perm-level-2-item">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="hr" value="staff_management" />
                <span>职员管理</span>
              </label>
            </div>
          </div>
        </div>

        <div className="perm-level-1">
          <div className="perm-level-1-item" data-perm="resource">
            <label className="perm-checkbox-label">
              <input type="checkbox" className="perm-l1-check" value="resource" />
              <span className="perm-arrow">▶</span>
              <strong>资源总库</strong>
            </label>
          </div>
          <div className="perm-level-2-container" data-parent="resource">
            <div className="perm-level-2-item has-level-3" data-sub="stock_inventory">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="resource" value="stock_inventory" />
                <span className="perm-arrow-sub">▶</span>
                <span>库存</span>
              </label>
            </div>
            <div className="perm-level-2-item">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="resource" value="dishware" />
                <span>碗碟</span>
              </label>
            </div>
            <div className="perm-level-2-item">
              <label className="perm-checkbox-label">
                <input type="checkbox" className="perm-l2-check" data-parent="resource" value="price_comparison" />
                <span>价格对比</span>
              </label>
            </div>
          </div>
        </div>

        <div className="perm-level-1">
          <div className="perm-level-1-item" data-perm="visual">
            <label className="perm-checkbox-label">
              <input type="checkbox" className="perm-l1-check" value="visual" />
              <strong>视觉管理</strong>
            </label>
          </div>
        </div>
      </div>

      <div className="perm-detail-card">
        <div className="perm-detail-placeholder">
          <i className="fas fa-hand-pointer" style={{ fontSize: 48, color: '#d1d5db', marginBottom: 15 }} />
          <p style={{ color: '#9ca3af', fontSize: 14 }}>
            点击左侧带有箭头的选项
            <br />
            查看详细配置
          </p>
        </div>

        <div className="perm-detail-content" id="perm-detail-content-main">
          <div className="perm-level-3-panel" data-for="kunzz_holdings">
            <div className="perm-detail-header">
              <strong>KUNZZ HOLDINGS SDN BHD</strong>
              <button type="button" className="perm-close-btn" onClick={onCloseDetail}>
                ×
              </button>
            </div>
            <div className="perm-level-3-section">
              <div className="perm-section-title">页面权限</div>
              <label>
                <input type="checkbox" className="perm-page-blueprint" data-brand="kunzz_holdings" value="blueprint" />{' '}
                企业蓝图
              </label>
            </div>
          </div>

          <div className="perm-level-3-panel" data-for="tokyo_cuisine">
            <div className="perm-detail-header">
              <strong>TOKYO JAPANESE CUISINE SDN BHD</strong>
              <button type="button" className="perm-close-btn" onClick={onCloseDetail}>
                ×
              </button>
            </div>
            <div className="perm-level-3-section">
              <div className="perm-section-title">店面</div>
              <div className="perm-store-item" data-store="j1">
                <label className="perm-checkbox-label">
                  <span className="perm-arrow-store">▶</span>
                  <span>J1 (Midvalley Southkey)</span>
                </label>
                <div className="perm-store-content">
                  <div className="perm-section-title">页面权限</div>
                  <label>
                    <input
                      type="checkbox"
                      className="perm-page-schedule"
                      data-store="j1"
                      data-brand="tokyo_cuisine"
                      value="schedule"
                    />{' '}
                    员工排班表
                  </label>
                </div>
              </div>
              <div className="perm-store-item" data-store="j2">
                <label className="perm-checkbox-label">
                  <span className="perm-arrow-store">▶</span>
                  <span>J2 (Paradigm Mall)</span>
                </label>
                <div className="perm-store-content">
                  <div className="perm-section-title">页面权限</div>
                  <label>
                    <input
                      type="checkbox"
                      className="perm-page-schedule"
                      data-store="j2"
                      data-brand="tokyo_cuisine"
                      value="schedule"
                    />{' '}
                    员工排班表
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div className="perm-level-3-panel" data-for="tokyo_izakaya">
            <div className="perm-detail-header">
              <strong>TOKYO IZAKAYA SDN BHD</strong>
              <button type="button" className="perm-close-btn" onClick={onCloseDetail}>
                ×
              </button>
            </div>
            <div className="perm-level-3-section">
              <div className="perm-section-title">店面</div>
              <div className="perm-store-item" data-store="j3">
                <label className="perm-checkbox-label">
                  <span className="perm-arrow-store">▶</span>
                  <span>J3 (Desa Tebrau)</span>
                </label>
                <div className="perm-store-content">
                  <div className="perm-section-title">页面权限</div>
                  <label>
                    <input
                      type="checkbox"
                      className="perm-page-schedule"
                      data-store="j3"
                      data-brand="tokyo_izakaya"
                      value="schedule"
                    />{' '}
                    员工排班表
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div className="perm-level-3-panel" data-for="kpi_upload">
            <div className="perm-detail-header">
              <strong>数据上传</strong>
              <button type="button" className="perm-close-btn" onClick={onCloseDetail}>
                ×
              </button>
            </div>
            <div className="perm-level-3-section">
              <div className="perm-section-title">系统选项</div>
              <label>
                <input type="checkbox" className="perm-upload-system" value="j1" /> J1
              </label>
              <label>
                <input type="checkbox" className="perm-upload-system" value="j2" /> J2
              </label>
              <label>
                <input type="checkbox" className="perm-upload-system" value="j3" /> J3
              </label>
            </div>
            <div className="perm-level-3-section">
              <div className="perm-section-title">上传类型</div>
              <label>
                <input type="checkbox" className="perm-upload-type" value="kpi" /> KPI
              </label>
              <label>
                <input type="checkbox" className="perm-upload-type" value="cost" /> 成本
              </label>
            </div>
          </div>

          <div className="perm-level-3-panel" data-for="stock_inventory">
            <div className="perm-detail-header">
              <strong>库存</strong>
              <button type="button" className="perm-close-btn" onClick={onCloseDetail}>
                ×
              </button>
            </div>
            <div className="perm-level-3-section">
              <div className="perm-section-title">系统选项</div>
              <label>
                <input type="checkbox" className="perm-stock-system" value="central" /> 中央
              </label>
              <label>
                <input type="checkbox" className="perm-stock-system" value="j1" /> J1
              </label>
              <label>
                <input type="checkbox" className="perm-stock-system" value="j2" /> J2
              </label>
              <label>
                <input type="checkbox" className="perm-stock-system" value="j3" /> J3
              </label>
            </div>
            <div className="perm-level-3-section">
              <div className="perm-section-title">视图选项</div>
              <label>
                <input type="checkbox" className="perm-stock-view" value="list" /> 总库存
              </label>
              <label>
                <input type="checkbox" className="perm-stock-view" value="records" /> 进出货
              </label>
              <div
                style={{
                  marginLeft: 20,
                  marginTop: 5,
                  display: 'flex',
                  flexDirection: 'column',
                  gap: 5,
                  borderLeft: '2px solid #eee',
                  paddingLeft: 10,
                }}
              >
                <label style={{ fontSize: '0.9em' }}>
                  <input type="checkbox" className="perm-stock-shipper" value="is_shipper" /> 出货人
                </label>
              </div>
              <label>
                <input type="checkbox" className="perm-stock-view" value="remark" /> 货品备注
              </label>
              <label>
                <input type="checkbox" className="perm-stock-view" value="product" /> 货品种类
              </label>
              <div
                style={{
                  marginLeft: 20,
                  marginTop: 5,
                  display: 'flex',
                  flexDirection: 'column',
                  gap: 5,
                  borderLeft: '2px solid #eee',
                  paddingLeft: 10,
                }}
              >
                <label style={{ fontSize: '0.9em' }}>
                  <input type="checkbox" className="perm-stock-view" value="apply" /> 申请权限 (Applicant)
                </label>
                <label style={{ fontSize: '0.9em' }}>
                  <input type="checkbox" className="perm-stock-view" value="approve" /> 批准权限 (Approver)
                </label>
              </div>
              <label>
                <input type="checkbox" className="perm-stock-view" value="sot" /> 货品异常
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
