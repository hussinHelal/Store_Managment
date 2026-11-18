package com.storemanagement.mobile.ui

import android.content.Context
import android.hardware.camera2.CameraManager
import android.print.PrintManager
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp

@Composable
internal fun HardwareDiagnosticsScreen(modifier: Modifier = Modifier) {
    val context = LocalContext.current
    var barcode by remember { mutableStateOf("") }
    val cameraAvailable = runCatching {
        val manager = context.getSystemService(Context.CAMERA_SERVICE) as CameraManager
        manager.cameraIdList.isNotEmpty()
    }.getOrDefault(false)
    val printAvailable = context.getSystemService(Context.PRINT_SERVICE) is PrintManager
    Column(modifier.fillMaxWidth().verticalScroll(rememberScrollState()).padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        Text("تشخيص الأجهزة", style = androidx.compose.material3.MaterialTheme.typography.headlineSmall)
        Card(colors = CardDefaults.cardColors()) {
            Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                Text("الكاميرا: ${if (cameraAvailable) "متاحة على الجهاز" else "غير مكتشفة"}")
                Text("قارئ الباركود: استخدم زر المسح داخل شاشة الكاشير أو أدخل الرمز يدويًا هنا.")
                OutlinedTextField(value = barcode, onValueChange = { barcode = it }, label = { Text("اختبار قارئ متصل / رقم الباركود") }, singleLine = true, modifier = Modifier.fillMaxWidth())
                if (barcode.isNotBlank()) Text("تم استقبال: ${barcode.trim()}")
            }
        }
        Card(colors = CardDefaults.cardColors()) {
            Column(Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                Text("الطباعة: ${if (printAvailable) "خدمة الطباعة متاحة من النظام" else "خدمة الطباعة غير متاحة"}")
                Text("يمكن اختيار الطابعة من أمر الطباعة داخل الفاتورة.")
            }
        }
    }
}
