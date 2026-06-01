param(
    [string]$AdminToken,
    [string]$UserToken,
    [string]$GuestToken
)

$baseUrl = "http://127.0.0.1:8001/api"
$results = @()
$testId = 0

function Test-Endpoint {
    param(
        [string]$Method,
        [string]$Path,
        [string]$Token,
        [string]$Body,
        [string]$ContentType,
        [int]$ExpectedStatus,
        [string]$TestName,
        [string]$Phase
    )
    
    $script:testId++
    $url = "$baseUrl$Path"
    $headers = @{}
    if ($Token) { $headers["Authorization"] = "Bearer $Token" }
    
    try {
        $params = @{
            Uri = $url
            Method = $Method
            Headers = $headers
            UseBasicParsing = $true
            ErrorAction = 'Stop'
        }
        if ($Body) { $params.Body = $Body }
        if ($ContentType) { $params.ContentType = $ContentType }
        
        $resp = Invoke-WebRequest @params
        $status = $resp.StatusCode
        $content = $resp.Content
    } catch {
        $status = $_.Exception.Response.StatusCode.value__
        $reader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
        $content = $reader.ReadToEnd()
    }
    
    $pass = ($status -eq $ExpectedStatus)
    $result = [PSCustomObject]@{
        ID = $script:testId
        Phase = $Phase
        Test = $TestName
        Method = $Method
        Path = $Path
        Expected = $ExpectedStatus
        Actual = $status
        Pass = $pass
        Response = if ($content.Length -gt 200) { $content.Substring(0,200) + "..." } else { $content }
    }
    $script:results += $result
    
    $icon = if ($pass) { "PASS" } else { "FAIL" }
    Write-Host "[$icon] #$($script:testId) $TestName | Expected: $ExpectedStatus | Got: $status"
    
    return @{ Status = $status; Content = $content }
}

Write-Host "=========================================="
Write-Host "  LoveCards API Comprehensive Test Suite"
Write-Host "=========================================="
Write-Host ""

# ============================================
# PHASE 2: PUBLIC ROUTES (No auth required)
# ============================================
Write-Host "--- Phase 2: Public Routes (14 endpoints) ---"

Test-Endpoint -Method "GET" -Path "/cards" -ExpectedStatus 200 -TestName "P2-01 GET /cards (卡片列表)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/cards/hot" -ExpectedStatus 200 -TestName "P2-02 GET /cards/hot (热门卡片)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/cards/search?q=test" -ExpectedStatus 200 -TestName "P2-03 GET /cards/search (搜索)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/cards/18" -ExpectedStatus 200 -TestName "P2-04 GET /cards/:id (卡片详情)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/cards/18/comments" -ExpectedStatus 200 -TestName "P2-05 GET /cards/:id/comments (卡片评论)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/comments/1" -ExpectedStatus 200 -TestName "P2-06 GET /comments/:id (评论详情)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/tags" -ExpectedStatus 200 -TestName "P2-07 GET /tags (标签列表)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/tags/1" -ExpectedStatus 200 -TestName "P2-08 GET /tags/:id (标签详情)" -Phase "Public"
Test-Endpoint -Method "POST" -Path "/session/login" -Body '{"account":"admin@lovecards.cn","password":"123456"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P2-09 POST /session/login (登录)" -Phase "Public"
Test-Endpoint -Method "POST" -Path "/session/register" -Body '{"account":"testuser2@test.com","password":"123456"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P2-10 POST /session/register (注册)" -Phase "Public"
Test-Endpoint -Method "POST" -Path "/session/guest" -ExpectedStatus 200 -TestName "P2-11 POST /session/guest (访客登录)" -Phase "Public"
Test-Endpoint -Method "POST" -Path "/session/captcha" -Body '{"email":"test@test.com"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P2-12 POST /session/captcha (验证码)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/theme/config" -ExpectedStatus 200 -TestName "P2-13 GET /theme/config (主题配置)" -Phase "Public"
Test-Endpoint -Method "GET" -Path "/captcha/config" -ExpectedStatus 200 -TestName "P2-14 GET /captcha/config (验证配置)" -Phase "Public"

Write-Host ""

# ============================================
# PHASE 3: AUTH-ONLY ROUTES (JwtAuthCheck only)
# ============================================
Write-Host "--- Phase 3: Auth-Only Routes (14 endpoints) ---"

Test-Endpoint -Method "GET" -Path "/users/me" -Token $AdminToken -ExpectedStatus 200 -TestName "P3-01 GET /users/me (管理员信息)" -Phase "Auth"
Test-Endpoint -Method "PATCH" -Path "/users/me" -Token $AdminToken -Body '{"phone":"13800138001"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-02 PATCH /users/me (编辑信息)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/session/check" -Token $AdminToken -ExpectedStatus 200 -TestName "P3-03 GET /session/check (Token校验)" -Phase "Auth"
Test-Endpoint -Method "GET" -Path "/users/me/cards" -Token $AdminToken -ExpectedStatus 200 -TestName "P3-04 GET /users/me/cards (我的卡片)" -Phase "Auth"
Test-Endpoint -Method "GET" -Path "/users/me/comments" -Token $AdminToken -ExpectedStatus 200 -TestName "P3-05 GET /users/me/comments (我的评论)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/cards/batch" -Token $AdminToken -Body '{"method":"approve","ids":[18]}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-06 POST /cards/batch (批量操作)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/comments/batch" -Token $AdminToken -Body '{"method":"approve","ids":[1]}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-07 POST /comments/batch (批量操作)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/tags/batch" -Token $AdminToken -Body '{"method":"approve","ids":[1]}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-08 POST /tags/batch (批量操作)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/users/batch" -Token $AdminToken -Body '{"method":"approve","ids":[2]}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-09 POST /users/batch (批量操作)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/files/batch" -Token $AdminToken -Body '{"method":"delete","ids":[999]}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-10 POST /files/batch (批量操作)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/users/me/password" -Token $AdminToken -Body '{"old_password":"123456","new_password":"123456"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-11 POST /users/me/password (修改密码)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/users/me/email" -Token $AdminToken -Body '{"email":"admin@lovecards.cn"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-12 POST /users/me/email (绑定邮箱)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/users/me/email-captcha" -Token $AdminToken -Body '{"email":"admin@lovecards.cn"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P3-13 POST /users/me/email-captcha (邮箱验证码)" -Phase "Auth"
Test-Endpoint -Method "POST" -Path "/session/logout" -Token $AdminToken -ExpectedStatus 200 -TestName "P3-14 POST /session/logout (登出)" -Phase "Auth"

Write-Host ""

# ============================================
# PHASE 4: PROTECTED ROUTES (Admin with full caps)
# ============================================
Write-Host "--- Phase 4: Protected Routes (Admin - 66 endpoints) ---"

# Cards (5)
Test-Endpoint -Method "POST" -Path "/cards" -Token $AdminToken -Body '{"content":"Test card from automated test","data":"{\"title\":\"Test\"}"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-01 POST /cards (创建卡片)" -Phase "Admin"
Test-Endpoint -Method "PATCH" -Path "/cards/18" -Token $AdminToken -Body '{"content":"Updated by admin test"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-02 PATCH /cards/:id (编辑卡片)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/cards/18/like" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-03 POST /cards/:id/like (点赞)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/cards/18/comments" -Token $AdminToken -Body '{"content":"Test comment from admin"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-04 POST /cards/:id/comments (创建评论)" -Phase "Admin"
Test-Endpoint -Method "DELETE" -Path "/cards/17" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-05 DELETE /cards/:id (删除卡片)" -Phase "Admin"

# Comments (2)
Test-Endpoint -Method "PATCH" -Path "/comments/1" -Token $AdminToken -Body '{"content":"Updated comment by admin"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-06 PATCH /comments/:id (编辑评论)" -Phase "Admin"
Test-Endpoint -Method "DELETE" -Path "/comments/2" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-07 DELETE /comments/:id (删除评论)" -Phase "Admin"

# Tags (3)
Test-Endpoint -Method "POST" -Path "/tags" -Token $AdminToken -Body '{"name":"test-tag-admin"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-08 POST /tags (创建标签)" -Phase "Admin"
Test-Endpoint -Method "PATCH" -Path "/tags/1" -Token $AdminToken -Body '{"name":"updated-tag"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-09 PATCH /tags/:id (编辑标签)" -Phase "Admin"
Test-Endpoint -Method "DELETE" -Path "/tags/100" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-10 DELETE /tags/:id (删除标签)" -Phase "Admin"

# Users (4)
Test-Endpoint -Method "GET" -Path "/users" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-11 GET /users (用户列表)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/users/1" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-12 GET /users/:id (用户详情)" -Phase "Admin"
Test-Endpoint -Method "PATCH" -Path "/users/1" -Token $AdminToken -Body '{"phone":"13800138002"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-13 PATCH /users/:id (编辑用户)" -Phase "Admin"
Test-Endpoint -Method "DELETE" -Path "/users/999" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-14 DELETE /users/:id (删除用户)" -Phase "Admin"

# Roles (8)
Test-Endpoint -Method "GET" -Path "/roles" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-15 GET /roles (角色列表)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/roles/1" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-16 GET /roles/:id (角色详情)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/roles/3/capabilities" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-17 GET /roles/:id/capabilities (角色能力)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/roles" -Token $AdminToken -Body '{"name":"test-role","slug":"test-role","description":"Test role"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-18 POST /roles (创建角色)" -Phase "Admin"
Test-Endpoint -Method "PATCH" -Path "/roles/5" -Token $AdminToken -Body '{"description":"Updated test role"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-19 PATCH /roles/:id (编辑角色)" -Phase "Admin"
Test-Endpoint -Method "DELETE" -Path "/roles/5" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-20 DELETE /roles/:id (删除角色)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/roles/reseed" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-21 POST /roles/reseed (重新seed)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/roles/3/capabilities" -Token $AdminToken -Body '{"capabilities":"[\"cards.read\",\"cards.create\"]"}' -ContentType "application/json" -ExpectedStatus 200 -TestName "P4-22 POST /roles/:id/capabilities (分配能力)" -Phase "Admin"

# Permissions (2)
Test-Endpoint -Method "GET" -Path "/permissions" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-23 GET /permissions (权限列表)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/permissions/all" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-24 GET /permissions/all (全部权限)" -Phase "Admin"

# Config (8)
Test-Endpoint -Method "GET" -Path "/config" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-25 GET /config (获取配置)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/config/groups" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-26 GET /config/groups (配置组)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/config/init" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-27 POST /config/init (初始化)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/config/reload" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-28 POST /config/reload (重载)" -Phase "Admin"

# Files (7)
Test-Endpoint -Method "GET" -Path "/files" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-29 GET /files (文件列表)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/files/direct" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-30 POST /files/direct (直传凭证)" -Phase "Admin"
Test-Endpoint -Method "DELETE" -Path "/files/expired" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-31 DELETE /files/expired (清理)" -Phase "Admin"

# Likes (2)
Test-Endpoint -Method "GET" -Path "/likes" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-32 GET /likes (我的点赞)" -Phase "Admin"
Test-Endpoint -Method "DELETE" -Path "/likes/1" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-33 DELETE /likes/:id (取消点赞)" -Phase "Admin"

# Dashboard (1)
Test-Endpoint -Method "GET" -Path "/dashboard" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-34 GET /dashboard (控制台)" -Phase "Admin"

# System (1)
Test-Endpoint -Method "GET" -Path "/system/update" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-35 GET /system/update (系统更新)" -Phase "Admin"

# Theme (7)
Test-Endpoint -Method "GET" -Path "/theme/list" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-36 GET /theme/list (主题列表)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/theme/config" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-37 GET /theme/config (主题配置)" -Phase "Admin"
Test-Endpoint -Method "POST" -Path "/theme/freeze" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-38 POST /theme/freeze (固化配置)" -Phase "Admin"

# Captcha (4)
Test-Endpoint -Method "GET" -Path "/captcha/types" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-39 GET /captcha/types (验证驱动)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/captcha/drivers" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-40 GET /captcha/drivers (驱动详情)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/captcha/geetest_v4/meta" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-41 GET /captcha/:slug/meta (配置)" -Phase "Admin"

# Sender (6)
Test-Endpoint -Method "GET" -Path "/sender/types" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-42 GET /sender/types (消息驱动)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/sender/channels" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-43 GET /sender/channels (渠道)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/sender/templates" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-44 GET /sender/templates (模板)" -Phase "Admin"

# Storage (6)
Test-Endpoint -Method "GET" -Path "/storage/types" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-45 GET /storage/types (存储驱动)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/storage/channels" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-46 GET /storage/channels (渠道)" -Phase "Admin"
Test-Endpoint -Method "GET" -Path "/storage/channel-stats" -Token $AdminToken -ExpectedStatus 200 -TestName "P4-47 GET /storage/channel-stats (统计)" -Phase "Admin"

Write-Host ""

# ============================================
# PHASE 5: PERMISSION MATRIX TEST (User token)
# ============================================
Write-Host "--- Phase 5: Permission Matrix Test (User - should 403 on admin-only endpoints) ---"

# User should NOT have access to these (no .all capability)
Test-Endpoint -Method "GET" -Path "/users" -Token $UserToken -ExpectedStatus 403 -TestName "P5-01 GET /users (用户列表 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/users/1" -Token $UserToken -ExpectedStatus 403 -TestName "P5-02 GET /users/:id (用户详情 - 应403)" -Phase "User"
Test-Endpoint -Method "PATCH" -Path "/users/1" -Token $UserToken -Body '{"phone":"13800138003"}' -ContentType "application/json" -ExpectedStatus 403 -TestName "P5-03 PATCH /users/:id (编辑用户 - 应403)" -Phase "User"
Test-Endpoint -Method "DELETE" -Path "/users/1" -Token $UserToken -ExpectedStatus 403 -TestName "P5-04 DELETE /users/:id (删除用户 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/roles" -Token $UserToken -ExpectedStatus 403 -TestName "P5-05 GET /roles (角色列表 - 应403)" -Phase "User"
Test-Endpoint -Method "POST" -Path "/roles" -Token $UserToken -Body '{"name":"hack","slug":"hack"}' -ContentType "application/json" -ExpectedStatus 403 -TestName "P5-06 POST /roles (创建角色 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/permissions" -Token $UserToken -ExpectedStatus 403 -TestName "P5-07 GET /permissions (权限列表 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/config" -Token $UserToken -ExpectedStatus 403 -TestName "P5-08 GET /config (配置 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/dashboard" -Token $UserToken -ExpectedStatus 403 -TestName "P5-09 GET /dashboard (控制台 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/files" -Token $UserToken -ExpectedStatus 403 -TestName "P5-10 GET /files (文件列表 - 应403)" -Phase "User"
Test-Endpoint -Method "POST" -Path "/files/direct" -Token $UserToken -ExpectedStatus 403 -TestName "P5-11 POST /files/direct (直传 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/theme/list" -Token $UserToken -ExpectedStatus 403 -TestName "P5-12 GET /theme/list (主题列表 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/captcha/types" -Token $UserToken -ExpectedStatus 403 -TestName "P5-13 GET /captcha/types (验证驱动 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/sender/types" -Token $UserToken -ExpectedStatus 403 -TestName "P5-14 GET /sender/types (消息驱动 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/storage/types" -Token $UserToken -ExpectedStatus 403 -TestName "P5-15 GET /storage/types (存储驱动 - 应403)" -Phase "User"
Test-Endpoint -Method "GET" -Path "/system/update" -Token $UserToken -ExpectedStatus 403 -TestName "P5-16 GET /system/update (系统更新 - 应403)" -Phase "User"

Write-Host ""

# ============================================
# PHASE 6: GUEST TEST (should 401 on all protected)
# ============================================
Write-Host "--- Phase 6: Guest Test (should 401 on protected endpoints) ---"

Test-Endpoint -Method "GET" -Path "/users/me" -Token $GuestToken -ExpectedStatus 200 -TestName "P6-01 GET /users/me (访客信息)" -Phase "Guest"
Test-Endpoint -Method "GET" -Path "/cards" -ExpectedStatus 200 -TestName "P6-02 GET /cards (公开 - 无token)" -Phase "Guest"
Test-Endpoint -Method "POST" -Path "/cards" -Token $GuestToken -Body '{"content":"guest card"}' -ContentType "application/json" -ExpectedStatus 403 -TestName "P6-03 POST /cards (访客创建 - 应403)" -Phase "Guest"
Test-Endpoint -Method "PATCH" -Path "/cards/18" -Token $GuestToken -Body '{"content":"hack"}' -ContentType "application/json" -ExpectedStatus 403 -TestName "P6-04 PATCH /cards/:id (访客编辑 - 应403)" -Phase "Guest"
Test-Endpoint -Method "DELETE" -Path "/cards/18" -Token $GuestToken -ExpectedStatus 403 -TestName "P6-05 DELETE /cards/:id (访客删除 - 应403)" -Phase "Guest"
Test-Endpoint -Method "GET" -Path "/users" -Token $GuestToken -ExpectedStatus 403 -TestName "P6-06 GET /users (访客用户列表 - 应403)" -Phase "Guest"
Test-Endpoint -Method "GET" -Path "/dashboard" -Token $GuestToken -ExpectedStatus 403 -TestName "P6-07 GET /dashboard (访客控制台 - 应403)" -Phase "Guest"
Test-Endpoint -Method "GET" -Path "/roles" -Token $GuestToken -ExpectedStatus 403 -TestName "P6-08 GET /roles (访客角色 - 应403)" -Phase "Guest"

Write-Host ""

# ============================================
# PHASE 7: EDGE CASE TESTS
# ============================================
Write-Host "--- Phase 7: Edge Case Tests ---"

# Empty batch
Test-Endpoint -Method "POST" -Path "/cards/batch" -Token $AdminToken -Body '{"method":"approve","ids":[]}' -ContentType "application/json" -ExpectedStatus 400 -TestName "P7-01 POST /cards/batch 空ids (应400)" -Phase "Edge"

# Invalid batch method
Test-Endpoint -Method "POST" -Path "/cards/batch" -Token $AdminToken -Body '{"method":"invalid","ids":[18]}' -ContentType "application/json" -ExpectedStatus 400 -TestName "P7-02 POST /cards/batch 无效method (应400)" -Phase "Edge"

# Nonexistent card
Test-Endpoint -Method "GET" -Path "/cards/99999" -ExpectedStatus 404 -TestName "P7-03 GET /cards/99999 (不存在 - 应404)" -Phase "Edge"

# Nonexistent card update
Test-Endpoint -Method "PATCH" -Path "/cards/99999" -Token $AdminToken -Body '{"content":"test"}' -ContentType "application/json" -ExpectedStatus 404 -TestName "P7-04 PATCH /cards/99999 (不存在 - 应404)" -Phase "Edge"

# Invalid roles_id
Test-Endpoint -Method "PATCH" -Path "/users/1" -Token $AdminToken -Body '{"roles_id":[9999]}' -ContentType "application/json" -ExpectedStatus 400 -TestName "P7-05 PATCH /users/:id 无效roles_id (应400)" -Phase "Edge"

# Invalid capabilities
Test-Endpoint -Method "POST" -Path "/roles/3/capabilities" -Token $AdminToken -Body '{"capabilities":"[\"invalid.cap\"]"}' -ContentType "application/json" -ExpectedStatus 400 -TestName "P7-06 POST /roles/:id/capabilities 无效能力 (应400)" -Phase "Edge"

# No token on protected route
Test-Endpoint -Method "GET" -Path "/users" -ExpectedStatus 401 -TestName "P7-07 GET /users 无token (应401)" -Phase "Edge"

Write-Host ""

# ============================================
# SUMMARY
# ============================================
Write-Host "=========================================="
Write-Host "  TEST RESULTS SUMMARY"
Write-Host "=========================================="

$total = $results.Count
$passed = ($results | Where-Object { $_.Pass -eq $true }).Count
$failed = ($results | Where-Object { $_.Pass -eq $false }).Count

Write-Host "Total Tests: $total"
Write-Host "Passed: $passed"
Write-Host "Failed: $failed"
Write-Host "Pass Rate: $([math]::Round($passed/$total*100, 1))%"
Write-Host ""

if ($failed -gt 0) {
    Write-Host "--- FAILED TESTS ---"
    $results | Where-Object { $_.Pass -eq $false } | Format-Table ID, Test, Expected, Actual, Response -AutoSize
}

Write-Host ""
Write-Host "=========================================="
Write-Host "  END OF TEST SUITE"
Write-Host "=========================================="

# Return results for further processing
return $results
