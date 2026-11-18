package com.storemanagement.mobile.ui

import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.storemanagement.mobile.core.database.CachedCategory
import com.storemanagement.mobile.core.database.CachedProduct
import com.storemanagement.mobile.core.network.*
import java.util.UUID
import android.net.Uri

@Composable
internal fun InventoryOperationsScreen(
    branches: RemotePage<BranchDto>, stock: RemotePage<BranchStockDto>, movements: RemotePage<StockMovementDto>,
    transfers: RemotePage<StockTransferDto>, stocktakes: RemotePage<StocktakeDto>, stocktakeDetail: StocktakeDto?,
    products: List<CachedProduct>, categories: List<CachedCategory>,
    canViewBranchStock: Boolean, canManageBranchStock: Boolean, canViewMovements: Boolean, canManageMovements: Boolean,
    canViewStocktakes: Boolean, canManageStocktakes: Boolean, canApprove: Boolean,
    onRefresh: (String?, Long?, Long?, String) -> Unit, onRefreshTransfers: () -> Unit, onRefreshStocktakes: () -> Unit,
    onAdjustStock: (BranchStockDto, Int, String) -> Unit, onReorder: (BranchStockDto, Int) -> Unit,
    onSaveBranch: (Long?, BranchInputDto) -> Unit, onDeleteBranch: (Long) -> Unit,
    onSaveTransfer: (Long?, String, StockTransferInputDto) -> Unit, onTransferAction: (Long, String, String) -> Unit,
    onStartStocktake: (String, StocktakeInputDto) -> Unit, onLoadStocktake: (Long) -> Unit,
    onCount: (Long, Long, Int, String?) -> Unit, onStocktakeAction: (Long, String, String) -> Unit,
    modifier: Modifier = Modifier,
) {
    var tab by rememberSaveable { mutableStateOf(if (canViewBranchStock) "stock" else if (canViewStocktakes) "takes" else "moves") }
    var branchEditor by remember { mutableStateOf<BranchDto?>(null) }
    var branchCreate by remember { mutableStateOf(false) }
    var branchDelete by remember { mutableStateOf<BranchDto?>(null) }
    var transferCreate by remember { mutableStateOf(false) }
    var transferEditTarget by remember { mutableStateOf<StockTransferDto?>(null) }
    var transferCancel by remember { mutableStateOf<StockTransferDto?>(null) }
    var takeCreate by remember { mutableStateOf(false) }
    var takeDetail by remember { mutableStateOf<StocktakeDto?>(null) }
    var takeCancel by remember { mutableStateOf<Long?>(null) }
    var search by rememberSaveable { mutableStateOf("") }
    var branchFilter by rememberSaveable { mutableStateOf("") }
    var categoryFilter by rememberSaveable { mutableStateOf("") }
    var availability by rememberSaveable { mutableStateOf("all") }
    var stockEdit by remember { mutableStateOf<BranchStockDto?>(null) }
    Column(modifier.fillMaxSize().padding(horizontal = 12.dp, vertical = 8.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Row(Modifier.fillMaxWidth().horizontalScroll(rememberScrollState()), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
            buildList {
                if (canViewBranchStock) { add("stock" to "المخزون"); add("branches" to "الفروع"); add("transfers" to "التحويلات") }
                if (canViewStocktakes) add("takes" to "الجرد")
                if (canViewMovements) add("moves" to "الحركات")
            }.forEach { (key, title) ->
                FilterChip(selected = tab == key, onClick = { tab = key; search = ""; if (key == "transfers") onRefreshTransfers(); if (key == "takes") onRefreshStocktakes() }, label = { Text(title) })
            }
        }
        when (tab) {
            "stock" -> {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    OutlinedTextField(search, { search = it }, Modifier.weight(1f), label = { Text("بحث بالمنتج أو الباركود") }, singleLine = true)
                    IconButton(onClick = { onRefresh(search.ifBlank { null }, branchFilter.toLongOrNull(), categoryFilter.toLongOrNull(), availability) }) { Text("↻") }
                }
                Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    SimpleChoice("الفرع", listOf("" to "كل الفروع") + branches.items.map { it.id.toString() to it.name }, branchFilter) { branchFilter = it }
                    SimpleChoice("التصنيف", listOf("" to "كل التصنيفات") + categories.map { it.id.toString() to it.name }, categoryFilter) { categoryFilter = it }
                }
                Row(Modifier.horizontalScroll(rememberScrollState()), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
                    listOf("all" to "الكل", "low" to "منخفض", "out" to "نفد").forEach { (value, label) -> FilterChip(availability == value, { availability = value }, label = { Text(label) }) }
                    TextButton(onClick = { onRefresh(search.ifBlank { null }, branchFilter.toLongOrNull(), categoryFilter.toLongOrNull(), availability) }) { Text("تطبيق الفلاتر") }
                }
                stock.error?.let { ErrorBanner(it) }
                LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    items(stock.items.filter { search.isBlank() || it.product?.name?.contains(search, true) == true || it.product?.barcode?.contains(search, true) == true }, key = { it.id }) { item ->
                        Card { Column(Modifier.fillMaxWidth().padding(14.dp)) {
                            Text(item.product?.name ?: "منتج #${item.productId}", style = MaterialTheme.typography.titleMedium)
                            Text("${item.branch?.name ?: "الفرع ${item.branchId}"} • الكمية ${item.quantity} • حد إعادة الطلب ${item.reorderLevel}")
                            if (item.isLow) Text("المخزون عند حد التنبيه", color = MaterialTheme.colorScheme.error)
                            if (canManageMovements || canManageBranchStock) TextButton(onClick = { stockEdit = item }) { Text("تعديل الرصيد وحد التنبيه") }
                        } }
                    }
                    if (stock.items.isEmpty() && !stock.isLoading) item { Text("لا توجد أرصدة مخزون.", Modifier.padding(16.dp)) }
                }
            }
            "branches" -> {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                    Text("إدارة الفروع", style = MaterialTheme.typography.titleLarge)
                    if (canManageBranchStock) Button(onClick = { branchEditor = null; branchCreate = true }) { Text("إضافة فرع") }
                }
                branches.error?.let { ErrorBanner(it) }
                LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    items(branches.items, key = { it.id }) { branch ->
                        Card { Column(Modifier.fillMaxWidth().padding(14.dp)) {
                            Text(branch.name, style = MaterialTheme.typography.titleMedium)
                            Text("الرمز: ${branch.code} • ${if (branch.isMain) "الفرع الرئيسي" else if (branch.isActive) "نشط" else "متوقف"}")
                            branch.address?.takeIf(String::isNotBlank)?.let { Text("العنوان: $it") }
                            branch.phone?.takeIf(String::isNotBlank)?.let { Text("الهاتف: $it") }
                            if (canManageBranchStock) Row {
                                TextButton(onClick = { branchEditor = branch; branchCreate = true }) { Text("تعديل") }
                                if (!branch.isMain) TextButton(onClick = { branchDelete = branch }) { Text("حذف") }
                            }
                        } }
                    }
                    if (branches.items.isEmpty() && !branches.isLoading) item { Text("لا توجد فروع.", Modifier.padding(16.dp)) }
                }
            }
            "transfers" -> {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                    Text("تحويلات المخزون", style = MaterialTheme.typography.titleLarge)
                    if (canManageBranchStock) Button(onClick = { transferEditTarget = null; transferCreate = true }) { Text("تحويل جديد") }
                }
                transfers.error?.let { ErrorBanner(it) }
                LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    items(transfers.items, key = { it.id }) { transfer ->
                        Card { Column(Modifier.fillMaxWidth().padding(14.dp), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                            Text("${transfer.number} • ${transferStatus(transfer.status)}", style = MaterialTheme.typography.titleMedium)
                            Text("من ${transfer.fromBranch ?: "فرع ${transfer.fromBranchId}"} إلى ${transfer.toBranch ?: "فرع ${transfer.toBranchId}"}")
                            transfer.items.forEach { Text("${it.productName ?: "منتج ${it.productId}"} • ${it.quantity}") }
                            transfer.note?.takeIf(String::isNotBlank)?.let { Text("ملاحظة: $it") }
                            if (canManageBranchStock) Row(Modifier.horizontalScroll(rememberScrollState())) {
                                when (transfer.status) {
                                    "draft" -> { TextButton(onClick = { transferEditTarget = transfer; transferCreate = true }) { Text("تعديل") }; TextButton(onClick = { onTransferAction(transfer.id, "send", "") }) { Text("إرسال") }; TextButton(onClick = { onTransferAction(transfer.id, "delete", "") }) { Text("حذف المسودة") } }
                                    "sent" -> { TextButton(onClick = { onTransferAction(transfer.id, "receive", "") }) { Text("تأكيد الاستلام") }; TextButton(onClick = { transferCancel = transfer }) { Text("إلغاء التحويل") } }
                                }
                            }
                        } }
                    }
                    if (transfers.items.isEmpty() && !transfers.isLoading) item { Text("لا توجد تحويلات مسجلة.", Modifier.padding(16.dp)) }
                }
            }
            "takes" -> {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                    Text("جرد المخزون", style = MaterialTheme.typography.titleLarge)
                    if (canManageStocktakes) Button(onClick = { takeCreate = true }) { Text("بدء جرد") }
                }
                stocktakes.error?.let { ErrorBanner(it) }
                LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    items(stocktakes.items, key = { it.id }) { take ->
                        Card(onClick = { onLoadStocktake(take.id); takeDetail = take }) { Column(Modifier.fillMaxWidth().padding(14.dp)) {
                            val branchLabel = take.branchName ?: "فرع ${take.branchId}"
                            Text("${take.number} • $branchLabel", style = MaterialTheme.typography.titleMedium)
                            val scopeLabel = if (take.scope == "all") "كل المنتجات" else "تصنيف ${take.categoryId ?: "—"}"
                            Text("$scopeLabel • ${stocktakeStatus(take.status)} • ${take.items.size} صنف")
                        } }
                    }
                    if (stocktakes.items.isEmpty() && !stocktakes.isLoading) item { Text("لا توجد عمليات جرد.", Modifier.padding(16.dp)) }
                }
            }
            else -> {
                Text("حركات المخزون", style = MaterialTheme.typography.titleLarge)
                movements.error?.let { ErrorBanner(it) }
                LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    items(movements.items, key = { it.id }) { movement ->
                        Card { Column(Modifier.fillMaxWidth().padding(12.dp)) {
                            Text(movement.productName ?: "منتج #${movement.productId}", style = MaterialTheme.typography.titleSmall)
                            Text("${movementType(movement.type)} • ${if (movement.quantityDelta > 0) "+" else ""}${movement.quantityDelta} • الرصيد بعد الحركة ${movement.balanceAfter}")
                            Text("${movement.branchName ?: "الفرع ${movement.branchId}"} • ${movement.createdAt.orEmpty()}", style = MaterialTheme.typography.bodySmall)
                            movement.note?.let { Text(it, style = MaterialTheme.typography.bodySmall) }
                        } }
                    }
                    if (movements.items.isEmpty() && !movements.isLoading) item { Text("لا توجد حركات مخزون.", Modifier.padding(16.dp)) }
                }
            }
        }
    }
    if (branchCreate) BranchEditorDialog(branchEditor, onDismiss = { branchCreate = false }, onSave = { input -> onSaveBranch(branchEditor?.id, input); branchCreate = false })
    stockEdit?.let { item -> StockLevelDialog(item, canManageMovements, canManageBranchStock, onDismiss = { stockEdit = null }, onSaveAdjustment = { delta, reason -> onAdjustStock(item, delta, reason) }, onSaveReorder = { value -> onReorder(item, value) }) }
    branchDelete?.let { branch -> ConfirmOperationsDialog("حذف الفرع", "سيحذف الفرع ${branch.name} إذا لم يكن يحتوي على مخزون أو حركات.", "حذف", { onDeleteBranch(branch.id); branchDelete = null }, { branchDelete = null }) }
    if (transferCreate) TransferEditorDialog(transferEditTarget, branches.items.filter { it.isActive }, products, onDismiss = { transferCreate = false; transferEditTarget = null }, onSave = { from, to, lines, note ->
        val key = UUID.randomUUID().toString()
        onSaveTransfer(transferEditTarget?.id, key, StockTransferInputDto(from, to, note.ifBlank { null }, lines.map { (product, qty) -> StockTransferLineInputDto(product, qty) }, key))
        transferCreate = false; transferEditTarget = null
    })
    transferCancel?.let { transfer -> ReasonDialog("إلغاء التحويل", onDismiss = { transferCancel = null }, onConfirm = { reason -> onTransferAction(transfer.id, "cancel", reason); transferCancel = null }) }
    if (takeCreate) StocktakeStartDialog(branches.items.filter { it.isActive }, categories, onDismiss = { takeCreate = false }, onStart = { branchId, scope, categoryId, note ->
        val key = UUID.randomUUID().toString()
        onStartStocktake(key, StocktakeInputDto(branchId, scope, categoryId, note.ifBlank { null }, key)); takeCreate = false
    })
    val detail = stocktakeDetail ?: takeDetail
    if (takeDetail != null) StocktakeDetailDialog(detail, canManageStocktakes, canApprove, onDismiss = { takeDetail = null }, onCount = onCount, onAction = { id, action, reason -> if (action == "cancel") takeCancel = id else onStocktakeAction(id, action, reason) })
    takeCancel?.let { id -> ReasonDialog("إلغاء الجرد", onDismiss = { takeCancel = null }, onConfirm = { reason -> onStocktakeAction(id, "cancel", reason); takeCancel = null }) }
}

@Composable
private fun StockLevelDialog(item: BranchStockDto, canAdjust: Boolean, canChangeReorder: Boolean, onDismiss: () -> Unit, onSaveAdjustment: (Int, String) -> Unit, onSaveReorder: (Int) -> Unit) {
    var delta by remember(item) { mutableStateOf("") }; var reason by remember(item) { mutableStateOf("") }; var reorder by remember(item) { mutableStateOf(item.reorderLevel.toString()) }
    AlertDialog(onDismissRequest = onDismiss, title = { Text("${item.product?.name ?: "المنتج"} • ${item.branch?.name ?: "الفرع"}") },
        text = { Column(Modifier.verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(7.dp)) {
            Text("الرصيد الحالي: ${item.quantity}")
            OutlinedTextField(delta, { delta = it.filterIndexed { index, c -> c.isDigit() || index == 0 && c == '-' } }, label = { Text("تعديل الكمية (+ أو -)") }, singleLine = true)
            OutlinedTextField(reason, { reason = it }, label = { Text("سبب تعديل المخزون") }, minLines = 2)
            HorizontalDivider()
            OutlinedTextField(reorder, { reorder = it.filter(Char::isDigit) }, label = { Text("حد إعادة الطلب") }, singleLine = true)
        } },
        confirmButton = { Row { if (canChangeReorder) TextButton(onClick = { reorder.toIntOrNull()?.let(onSaveReorder); onDismiss() }, enabled = reorder.toIntOrNull() != null) { Text("حفظ الحد") }; if (canAdjust) TextButton(onClick = { delta.toIntOrNull()?.let { onSaveAdjustment(it, reason) }; onDismiss() }, enabled = (delta.toIntOrNull() ?: 0) != 0 && reason.trim().length >= 5) { Text("تسجيل حركة") } } },
        dismissButton = { TextButton(onClick = onDismiss) { Text("إغلاق") } })
}

@Composable
private fun BranchEditorDialog(branch: BranchDto?, onDismiss: () -> Unit, onSave: (BranchInputDto) -> Unit) {
    var name by remember(branch) { mutableStateOf(branch?.name.orEmpty()) }
    var code by remember(branch) { mutableStateOf(branch?.code.orEmpty()) }
    var address by remember(branch) { mutableStateOf(branch?.address.orEmpty()) }
    var phone by remember(branch) { mutableStateOf(branch?.phone.orEmpty()) }
    var active by remember(branch) { mutableStateOf(branch?.isActive ?: true) }
    AlertDialog(onDismissRequest = onDismiss, title = { Text(if (branch == null) "إضافة فرع" else "تعديل الفرع") },
        text = { Column(Modifier.verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            OutlinedTextField(name, { name = it }, label = { Text("اسم الفرع") }, singleLine = true)
            OutlinedTextField(code, { code = it }, label = { Text("رمز الفرع") }, singleLine = true)
            OutlinedTextField(address, { address = it }, label = { Text("العنوان") }, singleLine = true)
            OutlinedTextField(phone, { phone = it }, label = { Text("الهاتف") }, singleLine = true)
            if (branch != null && !branch.isMain) Row(verticalAlignment = Alignment.CenterVertically) { Checkbox(active, { active = it }); Text("الفرع نشط") }
        } }, confirmButton = { TextButton(onClick = { onSave(BranchInputDto(name.trim(), code.trim(), address.trim().ifBlank { null }, phone.trim().ifBlank { null }, active)) }, enabled = name.isNotBlank() && code.isNotBlank()) { Text("حفظ") } }, dismissButton = { TextButton(onClick = onDismiss) { Text("إلغاء") } })
}

@Composable
private fun TransferEditorDialog(transfer: StockTransferDto?, branches: List<BranchDto>, products: List<CachedProduct>, onDismiss: () -> Unit, onSave: (Long, Long, List<Pair<Long, Int>>, String) -> Unit) {
    var from by remember(transfer) { mutableStateOf(transfer?.fromBranchId?.toString() ?: branches.firstOrNull()?.id?.toString().orEmpty()) }
    var to by remember(transfer) { mutableStateOf(transfer?.toBranchId?.toString() ?: branches.getOrNull(1)?.id?.toString().orEmpty()) }
    var productIds by remember(transfer) { mutableStateOf(transfer?.items?.map { it.productId.toString() }?.ifEmpty { listOf("") } ?: listOf("")) }
    var quantities by remember(transfer) { mutableStateOf(transfer?.items?.map { it.quantity.toString() }?.ifEmpty { listOf("1") } ?: listOf("1")) }
    var note by remember(transfer) { mutableStateOf(transfer?.note.orEmpty()) }
    val validLines = productIds.indices.mapNotNull { index -> productIds[index].toLongOrNull()?.let { id -> quantities[index].toIntOrNull()?.takeIf { it > 0 }?.let { id to it } } }
    AlertDialog(onDismissRequest = onDismiss, title = { Text(if (transfer == null) "إنشاء تحويل مخزون" else "تعديل مسودة التحويل") }, text = { Column(Modifier.verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        SimpleChoice("من الفرع", branches.map { it.id.toString() to it.name }, from) { from = it }
        SimpleChoice("إلى الفرع", branches.map { it.id.toString() to it.name }, to) { to = it }
        productIds.indices.forEach { index ->
            Card { Column(Modifier.fillMaxWidth().padding(8.dp), verticalArrangement = Arrangement.spacedBy(6.dp)) {
                SimpleChoice("المنتج ${index + 1}", products.map { it.id.toString() to "${it.name} (المخزون ${it.stock})" }, productIds[index]) { value -> productIds = productIds.toMutableList().also { it[index] = value } }
                Row(verticalAlignment = Alignment.CenterVertically) {
                    OutlinedTextField(quantities[index], { value -> quantities = quantities.toMutableList().also { it[index] = value.filter(Char::isDigit) } }, Modifier.weight(1f), label = { Text("الكمية") }, singleLine = true)
                    if (productIds.size > 1) TextButton(onClick = { productIds = productIds.filterIndexed { i, _ -> i != index }; quantities = quantities.filterIndexed { i, _ -> i != index } }) { Text("إزالة") }
                }
            } }
        }
        TextButton(onClick = { productIds = productIds + ""; quantities = quantities + "1" }) { Text("إضافة صنف للتحويل") }
        OutlinedTextField(note, { note = it }, label = { Text("ملاحظة") }, minLines = 2)
    } }, confirmButton = { TextButton(onClick = { onSave(from.toLong(), to.toLong(), validLines, note) }, enabled = from.toLongOrNull() != null && to.toLongOrNull() != null && from != to && validLines.size == productIds.size && validLines.map { it.first }.distinct().size == validLines.size) { Text(if (transfer == null) "إنشاء" else "حفظ التعديلات") } }, dismissButton = { TextButton(onClick = onDismiss) { Text("إلغاء") } })
}

@Composable
private fun StocktakeStartDialog(branches: List<BranchDto>, categories: List<CachedCategory>, onDismiss: () -> Unit, onStart: (Long, String, Long?, String) -> Unit) {
    var branch by remember { mutableStateOf(branches.firstOrNull()?.id?.toString().orEmpty()) }; var scope by remember { mutableStateOf("all") }; var category by remember { mutableStateOf("") }; var note by remember { mutableStateOf("") }
    AlertDialog(onDismissRequest = onDismiss, title = { Text("بدء جرد جديد") }, text = { Column(Modifier.verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        SimpleChoice("الفرع", branches.map { it.id.toString() to it.name }, branch) { branch = it }
        Row { FilterChip(scope == "all", { scope = "all" }, label = { Text("كل المنتجات") }); Spacer(Modifier.width(8.dp)); FilterChip(scope == "category", { scope = "category" }, label = { Text("تصنيف") }) }
        if (scope == "category") SimpleChoice("التصنيف", categories.map { it.id.toString() to it.name }, category) { category = it }
        OutlinedTextField(note, { note = it }, label = { Text("ملاحظة") }, minLines = 2)
    } }, confirmButton = { TextButton(onClick = { onStart(branch.toLong(), scope, category.toLongOrNull(), note) }, enabled = branch.toLongOrNull() != null && (scope == "all" || category.toLongOrNull() != null)) { Text("بدء الجرد") } }, dismissButton = { TextButton(onClick = onDismiss) { Text("إلغاء") } })
}

@Composable
private fun StocktakeDetailDialog(take: StocktakeDto?, canManage: Boolean, canApprove: Boolean, onDismiss: () -> Unit, onCount: (Long, Long, Int, String?) -> Unit, onAction: (Long, String, String) -> Unit) {
    if (take == null) return
    AlertDialog(onDismissRequest = onDismiss, title = { Text("${take.number} • ${stocktakeStatus(take.status)}") }, text = { Column(Modifier.heightIn(max = 480.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Text("${take.branchName.orEmpty()} • ${if (take.scope == "all") "كل المنتجات" else "تصنيف ${take.categoryId}"}")
        take.items.forEach { item ->
            var counted by remember(item.id, item.countedQty) { mutableStateOf(item.countedQty?.toString().orEmpty()) }
            var note by remember(item.id) { mutableStateOf(item.note.orEmpty()) }
            Card { Column(Modifier.fillMaxWidth().padding(8.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                Text(item.productName ?: "منتج #${item.productId}", style = MaterialTheme.typography.titleSmall)
                Text("باركود ${item.barcode ?: "—"} • رصيد النظام ${item.systemQty} • الفرق ${item.difference ?: "—"}")
                if (canManage && take.status in setOf("counting", "review")) Row(verticalAlignment = Alignment.CenterVertically) {
                    OutlinedTextField(counted, { counted = it.filter(Char::isDigit) }, Modifier.weight(1f), label = { Text("الكمية المعدودة") }, singleLine = true)
                    TextButton(onClick = { counted.toIntOrNull()?.let { onCount(take.id, item.id, it, note.ifBlank { null }) } }, enabled = counted.toIntOrNull() != null) { Text("حفظ") }
                }
                if (canManage && take.status in setOf("counting", "review")) OutlinedTextField(note, { note = it }, label = { Text("ملاحظة الصنف") }, singleLine = true)
            } }
        }
        if (canManage) Row(Modifier.horizontalScroll(rememberScrollState())) {
            if (take.status in setOf("draft", "counting")) TextButton(onClick = { onAction(take.id, "review", "") }) { Text("إرسال للمراجعة") }
            if (take.status == "review" && canApprove) TextButton(onClick = { onAction(take.id, "approve", "") }) { Text("اعتماد وتطبيق الفروقات") }
            if (take.status in setOf("draft", "counting", "review")) TextButton(onClick = { onAction(take.id, "cancel", "") }) { Text("إلغاء") }
        }
    } }, confirmButton = { TextButton(onClick = onDismiss) { Text("إغلاق") } })
}

@Composable
private fun SimpleChoice(label: String, options: List<Pair<String, String>>, selected: String, onSelect: (String) -> Unit) {
    var expanded by remember { mutableStateOf(false) }
    Box {
        OutlinedButton(onClick = { expanded = true }, modifier = Modifier.fillMaxWidth()) { Text("$label: ${options.firstOrNull { it.first == selected }?.second ?: "اختر"}") }
        DropdownMenu(expanded, { expanded = false }) { options.forEach { (value, title) -> DropdownMenuItem(text = { Text(title) }, onClick = { onSelect(value); expanded = false }) } }
    }
}

@Composable
private fun ConfirmOperationsDialog(title: String, body: String, confirm: String, onConfirm: () -> Unit, onDismiss: () -> Unit) {
    AlertDialog(onDismissRequest = onDismiss, title = { Text(title) }, text = { Text(body) }, confirmButton = { TextButton(onClick = onConfirm) { Text(confirm) } }, dismissButton = { TextButton(onClick = onDismiss) { Text("إلغاء") } })
}

@Composable
private fun ReasonDialog(title: String, onDismiss: () -> Unit, onConfirm: (String) -> Unit) {
    var reason by remember { mutableStateOf("") }
    AlertDialog(onDismissRequest = onDismiss, title = { Text(title) }, text = { OutlinedTextField(reason, { reason = it }, label = { Text("سبب الإلغاء") }, minLines = 2) }, confirmButton = { TextButton(onClick = { onConfirm(reason.trim()) }, enabled = reason.trim().length >= 5) { Text("تأكيد") } }, dismissButton = { TextButton(onClick = onDismiss) { Text("رجوع") } })
}

private fun transferStatus(value: String) = when (value) { "draft" -> "مسودة"; "sent" -> "في الطريق"; "received" -> "مستلم"; else -> "ملغى" }
private fun stocktakeStatus(value: String) = when (value) { "draft" -> "مسودة"; "counting" -> "قيد العد"; "review" -> "قيد المراجعة"; "approved" -> "معتمد"; else -> "ملغى" }
private fun movementType(value: String) = when (value) { "opening" -> "رصيد افتتاحي"; "purchase" -> "استلام مشتريات"; "sale" -> "بيع"; "refund" -> "مرتجع"; "damage" -> "هالك"; "transfer_out" -> "تحويل صادر"; "transfer_in" -> "تحويل وارد"; "count_adjust" -> "تسوية جرد"; "manual_adjust" -> "تعديل يدوي"; else -> value }

@Composable
internal fun UsedPhonesScreen(page: RemotePage<UsedPhoneDto>, canManage: Boolean, onRefresh: (String?, String?, String?) -> Unit, onSave: (Long?, UsedPhoneInputDto, Uri?) -> Unit, onDelete: (Long) -> Unit, modifier: Modifier = Modifier) {
    var search by rememberSaveable { mutableStateOf("") }; var status by rememberSaveable { mutableStateOf("") }; var condition by rememberSaveable { mutableStateOf("") }
    var editor by remember { mutableStateOf<UsedPhoneDto?>(null) }; var showForm by remember { mutableStateOf(false) }; var deleting by remember { mutableStateOf<UsedPhoneDto?>(null) }
    Column(modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            OutlinedTextField(search, { search = it }, Modifier.weight(1f), label = { Text("بحث بالماركة أو الموديل أو IMEI أو البائع") }, singleLine = true)
            IconButton(onClick = { onRefresh(search.ifBlank { null }, status.ifBlank { null }, condition.ifBlank { null }) }) { Text("↻") }
        }
        Row(Modifier.horizontalScroll(rememberScrollState()), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
            listOf("" to "كل الحالات", "in_stock" to "في المخزون", "reserved" to "محجوز", "sold" to "مباع", "returned" to "مرتجع").forEach { (value, label) -> FilterChip(status == value, { status = value; onRefresh(search.ifBlank { null }, value.ifBlank { null }, condition.ifBlank { null }) }, label = { Text(label) }) }
        }
        Row(Modifier.horizontalScroll(rememberScrollState()), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
            listOf("" to "كل الحالات الفنية", "excellent" to "ممتاز", "good" to "جيد", "fair" to "مقبول", "poor" to "ضعيف").forEach { (value, label) -> FilterChip(condition == value, { condition = value; onRefresh(search.ifBlank { null }, status.ifBlank { null }, value.ifBlank { null }) }, label = { Text(label) }) }
            if (canManage) Button(onClick = { editor = null; showForm = true }) { Text("إضافة هاتف") }
        }
        page.error?.let { ErrorBanner(it) }
        if (page.isLoading && page.items.isEmpty()) LoadingRow()
        LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            items(page.items, key = { it.id }) { phone ->
                Card { Column(Modifier.fillMaxWidth().padding(14.dp), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                    Text("${phone.brand} ${phone.model}", style = MaterialTheme.typography.titleMedium)
                    Text("IMEI: ${phone.imei ?: "—"} • تسلسلي: ${phone.serialNumber ?: "—"}")
                    Text("الحالة الفنية: ${conditionLabel(phone.condition)} • الحالة: ${usedPhoneStatus(phone.status)}")
                    Text("سعر الشراء ${phone.purchasePrice} ج.م • سعر البيع ${phone.askingPrice} ج.م")
                    Text("البائع: ${phone.sellerName} • ${phone.sellerPhone ?: "بدون هاتف"}")
                    Text("تاريخ الشراء: ${phone.purchaseDate}")
                    phone.notes?.takeIf(String::isNotBlank)?.let { Text("ملاحظات: $it") }
                    phone.imageUrl?.let { Text("الصورة مسجلة: $it", style = MaterialTheme.typography.bodySmall) }
                    if (canManage) Row { TextButton(onClick = { editor = phone; showForm = true }) { Text("تعديل") }; TextButton(onClick = { deleting = phone }) { Text("حذف") } }
                } }
            }
            if (page.items.isEmpty() && !page.isLoading) item { Text("لا توجد هواتف مستعملة.", Modifier.padding(16.dp)) }
        }
    }
    if (showForm) UsedPhoneFormDialog(editor, onDismiss = { showForm = false }, onSave = { input, image -> onSave(editor?.id, input, image); showForm = false })
    deleting?.let { phone -> ConfirmOperationsDialog("حذف الهاتف", "حذف سجل ${phone.brand} ${phone.model}؟", "حذف", { onDelete(phone.id); deleting = null }, { deleting = null }) }
}

@Composable
private fun UsedPhoneFormDialog(phone: UsedPhoneDto?, onDismiss: () -> Unit, onSave: (UsedPhoneInputDto, Uri?) -> Unit) {
    var brand by remember(phone) { mutableStateOf(phone?.brand.orEmpty()) }; var model by remember(phone) { mutableStateOf(phone?.model.orEmpty()) }
    var imei by remember(phone) { mutableStateOf(phone?.imei.orEmpty()) }; var serial by remember(phone) { mutableStateOf(phone?.serialNumber.orEmpty()) }
    var condition by remember(phone) { mutableStateOf(phone?.condition ?: "good") }; var purchase by remember(phone) { mutableStateOf(phone?.purchasePrice.orEmpty()) }
    var asking by remember(phone) { mutableStateOf(phone?.askingPrice.orEmpty()) }; var date by remember(phone) { mutableStateOf(phone?.purchaseDate ?: java.time.LocalDate.now().toString()) }
    var seller by remember(phone) { mutableStateOf(phone?.sellerName.orEmpty()) }; var sellerPhone by remember(phone) { mutableStateOf(phone?.sellerPhone.orEmpty()) }
    var status by remember(phone) { mutableStateOf(phone?.status ?: "in_stock") }; var notes by remember(phone) { mutableStateOf(phone?.notes.orEmpty()) }
    var image by remember(phone) { mutableStateOf<Uri?>(null) }; var removeImage by remember(phone) { mutableStateOf(false) }
    val imagePicker = rememberLauncherForActivityResult(ActivityResultContracts.GetContent()) { image = it }
    AlertDialog(onDismissRequest = onDismiss, title = { Text(if (phone == null) "تسجيل هاتف مستعمل" else "تعديل الهاتف المستعمل") },
        text = { Column(Modifier.heightIn(max = 520.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(6.dp)) {
            OutlinedTextField(brand, { brand = it }, label = { Text("الماركة") }, singleLine = true)
            OutlinedTextField(model, { model = it }, label = { Text("الموديل") }, singleLine = true)
            OutlinedTextField(imei, { imei = it }, label = { Text("IMEI") }, singleLine = true)
            OutlinedTextField(serial, { serial = it }, label = { Text("الرقم التسلسلي") }, singleLine = true)
            SimpleChoice("الحالة الفنية", listOf("excellent" to "ممتاز", "good" to "جيد", "fair" to "مقبول", "poor" to "ضعيف"), condition) { condition = it }
            OutlinedTextField(purchase, { purchase = it }, label = { Text("سعر الشراء") }, singleLine = true)
            OutlinedTextField(asking, { asking = it }, label = { Text("سعر البيع المقترح") }, singleLine = true)
            OutlinedTextField(date, { date = it }, label = { Text("تاريخ الشراء (YYYY-MM-DD)") }, singleLine = true)
            OutlinedTextField(seller, { seller = it }, label = { Text("اسم البائع") }, singleLine = true)
            OutlinedTextField(sellerPhone, { sellerPhone = it }, label = { Text("هاتف البائع") }, singleLine = true)
            SimpleChoice("الحالة", listOf("in_stock" to "في المخزون", "reserved" to "محجوز", "sold" to "مباع", "returned" to "مرتجع"), status) { status = it }
            OutlinedButton(onClick = { imagePicker.launch("image/*") }, modifier = Modifier.fillMaxWidth()) { Text(if (image != null) "تم اختيار صورة" else "اختيار صورة الهاتف") }
            if (phone?.imageUrl != null) Row(verticalAlignment = Alignment.CenterVertically) { Checkbox(removeImage, { removeImage = it }); Text("إزالة الصورة الحالية") }
            OutlinedTextField(notes, { notes = it }, label = { Text("ملاحظات") }, minLines = 2)
        } }, confirmButton = { TextButton(onClick = { onSave(UsedPhoneInputDto(brand.trim(), model.trim(), imei.trim().ifBlank { null }, serial.trim().ifBlank { null }, condition, purchase.trim(), asking.trim(), date.trim(), seller.trim(), sellerPhone.trim().ifBlank { null }, status, notes.trim().ifBlank { null }, removeImage), image) }, enabled = brand.isNotBlank() && model.isNotBlank() && seller.isNotBlank() && purchase.toBigDecimalOrNull() != null && asking.toBigDecimalOrNull() != null && date.matches(Regex("\\d{4}-\\d{2}-\\d{2}"))) { Text("حفظ") } }, dismissButton = { TextButton(onClick = onDismiss) { Text("إلغاء") } })
}

@Composable
internal fun InstallationAppointmentsScreen(page: RemotePage<InstallationAppointmentDto>, canManage: Boolean, onRefresh: (String?, String?, String?) -> Unit, onSave: (Long?, InstallationAppointmentInputDto) -> Unit, onDelete: (Long) -> Unit, modifier: Modifier = Modifier) {
    var search by rememberSaveable { mutableStateOf("") }; var status by rememberSaveable { mutableStateOf("") }; var date by rememberSaveable { mutableStateOf("") }
    var editor by remember { mutableStateOf<InstallationAppointmentDto?>(null) }; var showForm by remember { mutableStateOf(false) }; var deleting by remember { mutableStateOf<InstallationAppointmentDto?>(null) }
    Column(modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        Row(verticalAlignment = Alignment.CenterVertically) { OutlinedTextField(search, { search = it }, Modifier.weight(1f), label = { Text("بحث بالعميل أو الهاتف أو الجهاز") }, singleLine = true); IconButton(onClick = { onRefresh(search.ifBlank { null }, status.ifBlank { null }, date.ifBlank { null }) }) { Text("↻") } }
        OutlinedTextField(date, { date = it }, Modifier.fillMaxWidth(), label = { Text("تاريخ الموعد (YYYY-MM-DD، اختياري)") }, singleLine = true)
        Row(Modifier.horizontalScroll(rememberScrollState()), horizontalArrangement = Arrangement.spacedBy(6.dp)) {
            listOf("" to "الكل", "pending_assignment" to "بانتظار الإسناد", "scheduled" to "مجدول", "in_progress" to "جارٍ", "completed" to "مكتمل", "cancelled" to "ملغى").forEach { (value, label) -> FilterChip(status == value, { status = value; onRefresh(search.ifBlank { null }, value.ifBlank { null }, date.ifBlank { null }) }, label = { Text(label) }) }
            if (canManage) Button(onClick = { editor = null; showForm = true }) { Text("حجز موعد") }
        }
        page.error?.let { ErrorBanner(it) }
        LazyColumn(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(8.dp)) {
            items(page.items, key = { it.id }) { appointment -> Card { Column(Modifier.fillMaxWidth().padding(14.dp), verticalArrangement = Arrangement.spacedBy(3.dp)) {
                Text("${appointment.customerName} • ${appointment.deviceModel}", style = MaterialTheme.typography.titleMedium)
                Text("${appointment.serviceType} • ${appointment.phone}")
                Text("الموعد: ${appointment.scheduledAt} • ${appointment.durationMinutes} دقيقة")
                Text("الفني: ${appointment.technicianName ?: appointment.technicianId?.toString() ?: "بانتظار الإسناد"} • ${appointmentStatus(appointment.status)}")
                appointment.address?.takeIf(String::isNotBlank)?.let { Text("العنوان: $it") }
                appointment.serviceDetails?.takeIf(String::isNotBlank)?.let { Text("تفاصيل الخدمة: $it") }
                appointment.notes?.takeIf(String::isNotBlank)?.let { Text("ملاحظات: $it") }
                if (canManage) Row { TextButton(onClick = { editor = appointment; showForm = true }) { Text("تعديل / تغيير الحالة") }; TextButton(onClick = { deleting = appointment }) { Text("حذف") } }
            } } }
            if (page.items.isEmpty() && !page.isLoading) item { Text("لا توجد مواعيد تركيب.", Modifier.padding(16.dp)) }
        }
    }
    if (showForm) AppointmentFormDialog(editor, onDismiss = { showForm = false }, onSave = { input -> onSave(editor?.id, input); showForm = false })
    deleting?.let { item -> ConfirmOperationsDialog("حذف الموعد", "حذف موعد ${item.customerName}؟", "حذف", { onDelete(item.id); deleting = null }, { deleting = null }) }
}

@Composable
private fun AppointmentFormDialog(appointment: InstallationAppointmentDto?, onDismiss: () -> Unit, onSave: (InstallationAppointmentInputDto) -> Unit) {
    var customer by remember(appointment) { mutableStateOf(appointment?.customerName.orEmpty()) }; var phone by remember(appointment) { mutableStateOf(appointment?.phone.orEmpty()) }
    var device by remember(appointment) { mutableStateOf(appointment?.deviceModel.orEmpty()) }; var service by remember(appointment) { mutableStateOf(appointment?.serviceType.orEmpty()) }
    var details by remember(appointment) { mutableStateOf(appointment?.serviceDetails.orEmpty()) }; var scheduled by remember(appointment) { mutableStateOf(appointment?.scheduledAt?.take(16) ?: "") }
    var duration by remember(appointment) { mutableStateOf((appointment?.durationMinutes ?: 60).toString()) }; var technician by remember(appointment) { mutableStateOf(appointment?.technicianId?.toString().orEmpty()) }
    var status by remember(appointment) { mutableStateOf(appointment?.status ?: "scheduled") }; var address by remember(appointment) { mutableStateOf(appointment?.address.orEmpty()) }; var notes by remember(appointment) { mutableStateOf(appointment?.notes.orEmpty()) }
    AlertDialog(onDismissRequest = onDismiss, title = { Text(if (appointment == null) "حجز موعد تركيب" else "تعديل موعد التركيب") }, text = { Column(Modifier.heightIn(max = 520.dp).verticalScroll(rememberScrollState()), verticalArrangement = Arrangement.spacedBy(6.dp)) {
        OutlinedTextField(customer, { customer = it }, label = { Text("اسم العميل") }, singleLine = true)
        OutlinedTextField(phone, { phone = it }, label = { Text("الهاتف") }, singleLine = true)
        OutlinedTextField(device, { device = it }, label = { Text("موديل الجهاز") }, singleLine = true)
        OutlinedTextField(service, { service = it }, label = { Text("نوع الخدمة") }, singleLine = true)
        OutlinedTextField(details, { details = it }, label = { Text("تفاصيل الخدمة") }, minLines = 2)
        OutlinedTextField(scheduled, { scheduled = it }, label = { Text("تاريخ ووقت الموعد (YYYY-MM-DDTHH:MM)") }, singleLine = true)
        OutlinedTextField(duration, { duration = it.filter(Char::isDigit) }, label = { Text("المدة بالدقائق (15–480)") }, singleLine = true)
        OutlinedTextField(technician, { technician = it.filter(Char::isDigit) }, label = { Text("رقم الفني (اختياري)") }, singleLine = true)
        SimpleChoice("الحالة", listOf("pending_assignment" to "بانتظار الإسناد", "scheduled" to "مجدول", "in_progress" to "جارٍ", "completed" to "مكتمل", "cancelled" to "ملغى"), status) { status = it }
        OutlinedTextField(address, { address = it }, label = { Text("العنوان") }, singleLine = true)
        OutlinedTextField(notes, { notes = it }, label = { Text("ملاحظات") }, minLines = 2)
    } }, confirmButton = { TextButton(onClick = { onSave(InstallationAppointmentInputDto(customer.trim(), phone.trim(), device.trim(), service.trim(), details.trim().ifBlank { null }, scheduled.trim(), duration.toInt(), technician.toLongOrNull(), status, address.trim().ifBlank { null }, notes.trim().ifBlank { null })) }, enabled = customer.isNotBlank() && phone.isNotBlank() && device.isNotBlank() && service.isNotBlank() && scheduled.length >= 16 && (duration.toIntOrNull() ?: 0) in 15..480) { Text("حفظ") } }, dismissButton = { TextButton(onClick = onDismiss) { Text("إلغاء") } })
}

private fun conditionLabel(value: String) = when (value) { "excellent" -> "ممتاز"; "good" -> "جيد"; "fair" -> "مقبول"; "poor" -> "ضعيف"; else -> value }
private fun usedPhoneStatus(value: String) = when (value) { "in_stock" -> "في المخزون"; "reserved" -> "محجوز"; "sold" -> "مباع"; "returned" -> "مرتجع"; else -> value }
private fun appointmentStatus(value: String) = when (value) { "pending_assignment" -> "بانتظار الإسناد"; "scheduled" -> "مجدول"; "in_progress" -> "جارٍ"; "completed" -> "مكتمل"; "cancelled" -> "ملغى"; else -> value }
