const fs = require('fs');
const file = 'generatecode.php';
let content = fs.readFileSync(file, 'utf8');

const permStart = content.indexOf('<!-- 权限配置布局 -->');
const permEnd = content.indexOf('</div>\r\n\r\n                <div class="modal-buttons">');
if (permStart === -1 || permEnd === -1) {
    console.log('Could not find perm block');
    process.exit(1);
}
// Include the closing div in permBlock
const permBlock = content.substring(permStart, permEnd).trim();

const addSection = `<!-- 权限配置区块 -->
                    <div class="form-section">
                        <div class="form-section-header">权限配置</div>
                        <div class="form-section-content">
${permBlock.split('\n').map(l => '                            ' + l.trim()).join('\n')}
                            <div class="perm-warning" style="display:none;color:#dc3545;margin-top:10px;font-size:13px;">⚠️ 请至少选择一项用户权限</div>
                        </div>
                    </div>`;

const target1 = '</div>\r\n                        </div>\r\n                    </div>\r\n                    \r\n                    <div class="modal-buttons">\r\n                        <button type="submit" class="btn-action btn-save">\r\n                            添加职员';

content = content.replace(target1, `</div>\r\n                        </div>\r\n                    </div>\r\n\r\n                    ${addSection}\r\n                    \r\n                    <div class="modal-buttons">\r\n                        <button type="submit" class="btn-action btn-save">\r\n                            添加职员`);

const target2 = '</div>\r\n                        </div>\r\n                    </div>\r\n                    \r\n                    <div class="modal-buttons">\r\n                        <button type="submit" class="btn-action btn-save">\r\n                            <i class="fas fa-save"></i> 保存修改';

content = content.replace(target2, `</div>\r\n                        </div>\r\n                    </div>\r\n\r\n                    ${addSection}\r\n                    \r\n                    <div class="modal-buttons">\r\n                        <button type="submit" class="btn-action btn-save">\r\n                            <i class="fas fa-save"></i> 保存修改`);

fs.writeFileSync(file, content);
console.log('Modified generatecode.php successfully');
