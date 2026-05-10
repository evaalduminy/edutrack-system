<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="EduTrack & Archive API",
 *      description="توثيق واجهة برمجة التطبيقات لنظام إدارة وأرشفة الأبحاث (EduTrack API Documentation)",
 *      @OA\Contact(
 *          email="security@edutrack.local"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Main API Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="http",
 *      scheme="bearer",
 *      description="يرجى إدخال رمز الـ Bearer Token الخاص بك (Please enter your Bearer Token)"
 * )
 */
abstract class Controller
{
    //
}
