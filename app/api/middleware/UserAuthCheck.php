<?php

// namespace app\api\middleware;

// use app\common\Export;
// use jwt\Jwt;

// class UserAuthCheck
// {
//     public function handle($tDef_Request, \Closure $tDef_next)
//     {

//         //头部取authorization需要特殊伪静态
//         $token = $tDef_Request->header('authorization');
//         //是否有token
//         if ($token != null) {
//             //处理token
//             $token = preg_replace('/^Bearer\s+/', '', $token);
//             //验证token
//             $data = Jwt::CheckToken($token);
//             if ($data['status']) {
//                 //jwt校验通过并传递参数
//                 $tDef_Request->JwtData = $data['data'];
//             } else {
//                 //jwt校验不通过
//                 return Export::Create($data['msg'], 401, 'Token未通过校验，请重新登入');
//             }
//         } else {
//             return Export::Create(null, 401, 'Token不存在，请先登入');
//         }

//         return $tDef_next($tDef_Request);
//     }
// }
