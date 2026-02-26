<?php
$file = 'generatecode.php';
$content = file_get_contents($file);

$permStart = strpos($content, '<!-- 权限配置布局 -->');
$permEnd = strpos($content, '</div>' . "\r\n\r\n" . '                <div class="modal-buttons">');
if ($permStart === false || $permEnd === false) {
    echo "Could not find perm block\n";
    exit(1);
}
// Add 6 characters for '</div>'
$permBlock = substr($content, $permStart, $permEnd - $permStart + 6);
$permBlock = trim($permBlock);
$permLines = explode("\n", $permBlock);
$indentedPermBlock = implode("\n                            ", array_map('trim', $permLines));

$addSection = "<!-- 权限配置区块 -->\r\n" .
              "                    <div class=\"form-section\">\r\n" .
              "                        <div class=\"form-section-header\">权限配置</div>\r\n" .
              "                        <div class=\"form-section-content\">\r\n                            " .
              $indentedPermBlock . "\r\n" .
              "                            <div class=\"perm-warning\" style=\"display:none;color:#dc3545;margin-top:10px;font-size:13px;\">⚠️ 请至少选择一项用户权限</div>\r\n" .
              "                        </div>\r\n" .
              "                    </div>";

$target1 = '</div>' . "\r\n" .
           '                        </div>' . "\r\n" .
           '                    </div>' . "\r\n" .
           '                    ' . "\r\n" .
           '                    <div class="modal-buttons">' . "\r\n" .
           '                        <button type="submit" class="btn-action btn-save">' . "\r\n" .
           '                            添加职员';

$replacement1 = '</div>' . "\r\n" .
                '                        </div>' . "\r\n" .
                '                    </div>' . "\r\n\r\n" .
                '                    ' . $addSection . "\r\n" .
                '                    ' . "\r\n" .
                '                    <div class="modal-buttons">' . "\r\n" .
                '                        <button type="submit" class="btn-action btn-save">' . "\r\n" .
                '                            添加职员';

$content = str_replace($target1, $replacement1, $content, $count1);

$target2 = '</div>' . "\r\n" .
           '                        </div>' . "\r\n" .
           '                    </div>' . "\r\n" .
           '                    ' . "\r\n" .
           '                    <div class="modal-buttons">' . "\r\n" .
           '                        <button type="submit" class="btn-action btn-save">' . "\r\n" .
           '                            <i class="fas fa-save"></i> 保存修改';

$replacement2 = '</div>' . "\r\n" .
                '                        </div>' . "\r\n" .
                '                    </div>' . "\r\n\r\n" .
                '                    ' . $addSection . "\r\n" .
                '                    ' . "\r\n" .
                '                    <div class="modal-buttons">' . "\r\n" .
                '                        <button type="submit" class="btn-action btn-save">' . "\r\n" .
                '                            <i class="fas fa-save"></i> 保存修改';

$content = str_replace($target2, $replacement2, $content, $count2);

file_put_contents($file, $content);
echo "Modified generatecode.php successfully. Replacements: $count1, $count2\n";
?>
