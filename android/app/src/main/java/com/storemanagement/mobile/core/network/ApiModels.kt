package com.storemanagement.mobile.core.network

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ApiEnvelope<T>(
    val data: T,
)

@Serializable
data class ApiPage<T>(
    val data: List<T>,
    val links: PaginationLinks? = null,
    val meta: PaginationMeta? = null,
)

@Serializable
data class PaginationLinks(
    val next: String? = null,
    val prev: String? = null,
)

@Serializable
data class PaginationMeta(
    @SerialName("current_page") val currentPage: Int? = null,
    @SerialName("last_page") val lastPage: Int? = null,
    @SerialName("per_page") val perPage: Int? = null,
    val total: Int? = null,
)

@Serializable
data class TokenResponse(
    val data: TokenData,
)

@Serializable
data class TokenData(
    @SerialName("access_token") val accessToken: String,
    @SerialName("token_type") val tokenType: String,
    @SerialName("expires_at") val expiresAt: String? = null,
    val user: UserDto,
)

@Serializable
data class UserDto(
    val id: Long,
    val name: String,
    val username: String? = null,
    val roles: List<String> = emptyList(),
    val permissions: List<String> = emptyList(),
)

@Serializable
data class ProfileDto(
    val id: Long,
    val name: String,
    val username: String? = null,
    val email: String? = null,
)

@Serializable
data class ProfileInputDto(
    val name: String,
    val email: String? = null,
    @SerialName("current_password") val currentPassword: String? = null,
    val password: String? = null,
    @SerialName("password_confirmation") val passwordConfirmation: String? = null,
)

@Serializable
data class DamagedItemDto(
    val id: Long,
    @SerialName("product_id") val productId: Long,
    @SerialName("product_name") val productName: String,
    @SerialName("product_barcode") val productBarcode: String? = null,
    val quantity: Int,
    @SerialName("unit_value") val unitValue: String,
    @SerialName("total_value") val totalValue: String,
    val reason: String,
    @SerialName("reason_label") val reasonLabel: String,
    val status: String,
    @SerialName("damaged_on") val damagedOn: String,
    val notes: String? = null,
    @SerialName("stock_before") val stockBefore: Int,
    @SerialName("stock_after") val stockAfter: Int,
    @SerialName("void_reason") val voidReason: String? = null,
)

@Serializable
data class DamagedProductDto(
    val id: Long,
    val name: String,
    val barcode: String? = null,
    val price: String,
    val stock: Int,
)

@Serializable
data class DamagedItemInputDto(
    @SerialName("product_id") val productId: Long,
    val quantity: Int,
    val reason: String,
    @SerialName("unit_value") val unitValue: String? = null,
    @SerialName("damaged_on") val damagedOn: String,
    val notes: String? = null,
)

@Serializable
data class VoidDamagedItemInputDto(val reason: String)

@Serializable
data class ProductDto(
    val id: Long,
    val name: String,
    val price: String,
    val description: String? = null,
    val notes: String? = null,
    val barcode: String? = null,
    val stock: Int,
    val category: CategoryDto? = null,
    @SerialName("image_url") val imageUrl: String? = null,
    @SerialName("updated_at") val updatedAt: String? = null,
    @SerialName("cost_price") val costPrice: String? = null,
)

@Serializable
data class ProductInputDto(
    val name: String,
    val price: String,
    val description: String? = null,
    val notes: String? = null,
    val barcode: String? = null,
    val stock: Int,
    @SerialName("category_id") val categoryId: Long? = null,
    @SerialName("cost_price") val costPrice: String? = null,
)

@Serializable
data class SoldProductDto(
    val id: Long,
    val name: String,
    @SerialName("sold_quantity") val soldQuantity: Int,
)

@Serializable
data class SalesReportSummaryDto(
    @SerialName("gross_sales") val grossSales: String,
    val discounts: String? = null,
    val refunds: String,
    @SerialName("net_sales") val netSales: String,
    @SerialName("invoice_count") val invoiceCount: Int,
    @SerialName("refund_count") val refundCount: Int,
    @SerialName("average_ticket") val averageTicket: String,
    @SerialName("cost_of_goods") val costOfGoods: String,
    @SerialName("gross_profit") val grossProfit: String,
    @SerialName("missing_cost_quantity") val missingCostQuantity: Int,
    @SerialName("estimated_cost_quantity") val estimatedCostQuantity: Int = 0,
)

@Serializable
data class SalesReportPeriodDto(
    val period: String,
    @SerialName("net_sales") val netSales: String,
    @SerialName("invoice_count") val invoiceCount: Int,
)

@Serializable
data class SalesReportProductDto(
    @SerialName("product_id") val productId: Long? = null,
    val name: String,
    val quantity: Int,
    @SerialName("net_sales") val netSales: String,
)

@Serializable
data class SalesReportProductsDto(
    val top: List<SalesReportProductDto> = emptyList(),
    val bottom: List<SalesReportProductDto> = emptyList(),
)

@Serializable
data class SalesReportCategoryDto(
    val name: String,
    val quantity: Int,
    @SerialName("net_sales") val netSales: String,
)

@Serializable
data class SalesReportCashierDto(
    val name: String,
    @SerialName("invoice_count") val invoiceCount: Int,
    @SerialName("net_sales") val netSales: String,
)

@Serializable
data class SalesReportPaymentStatusDto(
    val status: String,
    @SerialName("invoice_count") val invoiceCount: Int,
)

@Serializable
data class SalesReportDto(
    val summary: SalesReportSummaryDto,
    val trend: List<SalesReportPeriodDto> = emptyList(),
    val products: SalesReportProductsDto = SalesReportProductsDto(),
    val categories: List<SalesReportCategoryDto> = emptyList(),
    val cashiers: List<SalesReportCashierDto> = emptyList(),
    @SerialName("payment_statuses") val paymentStatuses: List<SalesReportPaymentStatusDto> = emptyList(),
)

@Serializable
data class CategoryDto(
    val id: Long,
    val name: String,
    @SerialName("products_count") val productsCount: Int? = null,
)

@Serializable
data class CustomerDto(
    val id: Long,
    val name: String,
    val phone: String? = null,
    val address: String? = null,
)

@Serializable
data class SupplierDto(
    val id: Long,
    val name: String,
    val phone: String? = null,
    val email: String? = null,
    val address: String? = null,
    val notes: String? = null,
    @SerialName("opening_balance") val openingBalance: String,
    val balance: String,
    @SerialName("purchases_count") val purchasesCount: Int? = null,
)

@Serializable
data class SupplierInputDto(
    val name: String,
    val phone: String? = null,
    val email: String? = null,
    val address: String? = null,
    val notes: String? = null,
    @SerialName("opening_balance") val openingBalance: String? = null,
)

@Serializable
data class SupplierStatementDto(
    val supplier: SupplierDto,
    val summary: SupplierStatementSummaryDto,
    val purchases: SupplierStatementPageDto<SupplierPurchaseStatementDto>,
    val payments: SupplierStatementPageDto<SupplierPaymentStatementDto>,
)

@Serializable
data class SupplierStatementSummaryDto(
    @SerialName("opening_balance") val openingBalance: String,
    @SerialName("recorded_balance") val recordedBalance: String,
    @SerialName("unallocated_difference") val unallocatedDifference: String,
    val aging: Map<String, String> = emptyMap(),
)

@Serializable
data class SupplierStatementPageDto<T>(
    val data: List<T> = emptyList(),
    @SerialName("current_page") val currentPage: Int = 1,
    @SerialName("last_page") val lastPage: Int = 1,
    val total: Int = 0,
)

@Serializable
data class SupplierPurchaseStatementDto(
    val id: Long,
    @SerialName("invoice_number") val invoiceNumber: String,
    @SerialName("purchase_date") val purchaseDate: String? = null,
    @SerialName("total_amount") val totalAmount: String,
    @SerialName("paid_amount") val paidAmount: String,
    val remaining: String,
    val status: String,
)

@Serializable
data class SupplierPaymentStatementDto(
    val id: Long,
    @SerialName("receipt_number") val receiptNumber: String,
    @SerialName("payment_date") val paymentDate: String? = null,
    @SerialName("purchase_number") val purchaseNumber: String? = null,
    val amount: String,
    val notes: String? = null,
)

@Serializable
data class SupplierReceiptProductDto(
    val id: Long,
    val name: String,
    val barcode: String? = null,
    val stock: Int,
    @SerialName("cost_price") val costPrice: String? = null,
)

@Serializable
data class PurchaseReceiptLineInputDto(
    @SerialName("product_id") val productId: Long? = null,
    @SerialName("product_name") val productName: String? = null,
    val quantity: Int,
    @SerialName("unit_cost") val unitCost: String,
)

@Serializable
data class PurchaseReceiptInputDto(
    @SerialName("purchase_date") val purchaseDate: String,
    @SerialName("payment_type") val paymentType: String,
    @SerialName("paid_amount") val paidAmount: String,
    val items: List<PurchaseReceiptLineInputDto>,
    @SerialName("branch_id") val branchId: Long? = null,
    val notes: String? = null,
)

@Serializable
data class PurchaseReceiptDto(
    val id: Long,
    @SerialName("invoice_number") val invoiceNumber: String,
    @SerialName("total_amount") val totalAmount: String,
    @SerialName("paid_amount") val paidAmount: String,
    val remaining: String,
)

@Serializable
data class WalletOverviewDto(
    val summary: WalletSummaryDto,
    val wallets: List<WalletDto> = emptyList(),
)

@Serializable
data class NotificationDto(
    val id: Long,
    val title: String,
    val message: String,
    @SerialName("starts_at") val startsAt: String? = null,
    @SerialName("ends_at") val endsAt: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
    @SerialName("is_read") val isRead: Boolean = false,
)

@Serializable
data class MaintenanceDto(
    val id: Long,
    val name: String,
    val owner: String,
    val phone: String? = null,
    val address: String? = null,
    val description: String? = null,
    val notes: String? = null,
    val status: String,
    @SerialName("requested_date") val requestedDate: String,
    @SerialName("completed_date") val completedDate: String? = null,
)

@Serializable
data class MaintenanceInputDto(
    val name: String,
    val owner: String,
    val phone: String? = null,
    val address: String? = null,
    val description: String? = null,
    val notes: String? = null,
    val status: String,
    @SerialName("requested_date") val requestedDate: String,
)

@Serializable
data class WalletCashierDto(
    val wallets: List<CashierWalletDto> = emptyList(),
    val transactions: List<WalletCashierTransactionDto> = emptyList(),
    val outstanding: String = "0.00",
)

@Serializable
data class CashierWalletDto(
    val id: Long,
    val name: String,
    val provider: String,
    val identifier: String? = null,
    val balance: String,
    @SerialName("per_transaction_limit") val perTransactionLimit: String? = null,
    @SerialName("default_commission_percent") val defaultCommissionPercent: String? = null,
    @SerialName("default_commission_min") val defaultCommissionMin: String? = null,
    @SerialName("default_fee_percent") val defaultFeePercent: String? = null,
    @SerialName("default_fee_min") val defaultFeeMin: String? = null,
    @SerialName("default_fee_max") val defaultFeeMax: String? = null,
)

@Serializable
data class WalletCashierTransactionDto(
    val uuid: String,
    @SerialName("wallet_id") val walletId: Long,
    @SerialName("wallet_name") val walletName: String? = null,
    val type: String,
    val status: String,
    val amount: String,
    val commission: String,
    val fee: String,
    val counterparty: String? = null,
    @SerialName("customer_name") val customerName: String? = null,
    @SerialName("payment_method") val paymentMethod: String,
    val receivable: String,
    @SerialName("occurred_at") val occurredAt: String? = null,
    @SerialName("can_settle") val canSettle: Boolean = false,
    @SerialName("can_reverse") val canReverse: Boolean = false,
)

@Serializable
data class WalletCashierInputDto(
    @SerialName("wallet_id") val walletId: Long,
    val type: String,
    val amount: String,
    val counterparty: String,
    val commission: String? = null,
    val fee: String? = null,
    @SerialName("payment_method") val paymentMethod: String? = null,
    @SerialName("customer_name") val customerName: String? = null,
    val notes: String? = null,
)

@Serializable
data class SettleWalletTransactionInputDto(val amount: String? = null)

@Serializable
data class ReverseWalletTransactionInputDto(val reason: String)

@Serializable
data class WalletLedgerActionDto(val uuid: String)

@Serializable
data class CashierSearchResponseDto(
    val exact: Boolean = false,
    val data: List<CashierProductDto> = emptyList(),
)

@Serializable
data class CashierProductDto(
    val id: Long,
    val name: String,
    val price: String,
    val barcode: String? = null,
    val stock: Int,
    @SerialName("image_url") val imageUrl: String? = null,
)

@Serializable
data class TrackedDeviceDto(
    val id: Long,
    @SerialName("product_id") val productId: Long,
    @SerialName("product_name") val productName: String? = null,
    val imei: String? = null,
    @SerialName("serial_number") val serialNumber: String? = null,
    val status: String,
    @SerialName("customer_name") val customerName: String? = null,
    @SerialName("invoice_id") val invoiceId: Long? = null,
    val notes: String? = null,
)

@Serializable
data class TrackedDeviceInputDto(
    @SerialName("product_id") val productId: Long,
    val imei: String? = null,
    @SerialName("serial_number") val serialNumber: String? = null,
    val status: String,
    @SerialName("customer_name") val customerName: String? = null,
    @SerialName("invoice_id") val invoiceId: Long? = null,
    val notes: String? = null,
)

@Serializable
data class BranchStockDto(
    val id: Long,
    @SerialName("branch_id") val branchId: Long,
    val branch: BranchSummaryDto? = null,
    @SerialName("product_id") val productId: Long,
    val product: BranchProductSummaryDto? = null,
    val quantity: Int,
    @SerialName("reorder_level") val reorderLevel: Int,
    @SerialName("is_low") val isLow: Boolean = false,
)

@Serializable
data class BranchSummaryDto(val id: Long, val name: String)

@Serializable
data class BranchProductSummaryDto(val id: Long, val name: String, val barcode: String? = null)

@Serializable
data class StockAdjustmentInputDto(
    @SerialName("branch_id") val branchId: Long,
    @SerialName("product_id") val productId: Long,
    @SerialName("quantity_delta") val quantityDelta: Int,
    val reason: String,
)

@Serializable
data class StockMovementDto(
    val id: Long,
    @SerialName("branch_id") val branchId: Long,
    @SerialName("branch_name") val branchName: String? = null,
    @SerialName("product_id") val productId: Long,
    @SerialName("product_name") val productName: String? = null,
    val type: String,
    @SerialName("quantity_delta") val quantityDelta: Int,
    @SerialName("balance_after") val balanceAfter: Int,
    val note: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
)

@Serializable
data class BranchDto(
    val id: Long, val name: String, val code: String, val address: String? = null,
    val phone: String? = null, @SerialName("is_main") val isMain: Boolean = false,
    @SerialName("is_active") val isActive: Boolean = true,
)

@Serializable
data class ReorderLevelInputDto(@SerialName("reorder_level") val reorderLevel: Int)

@Serializable
data class BranchInputDto(
    val name: String, val code: String, val address: String? = null, val phone: String? = null,
    @SerialName("is_active") val isActive: Boolean = true,
)

@Serializable
data class StockTransferLineInputDto(@SerialName("product_id") val productId: Long, val quantity: Int)

@Serializable
data class StockTransferInputDto(
    @SerialName("from_branch_id") val fromBranchId: Long,
    @SerialName("to_branch_id") val toBranchId: Long,
    val note: String? = null,
    val items: List<StockTransferLineInputDto>,
    @SerialName("idempotency_key") val idempotencyKey: String,
)

@Serializable
data class StockTransferItemDto(@SerialName("product_id") val productId: Long, @SerialName("product_name") val productName: String? = null, val quantity: Int)

@Serializable
data class StockTransferDto(
    val id: Long, val number: String, @SerialName("from_branch_id") val fromBranchId: Long,
    @SerialName("from_branch") val fromBranch: String? = null, @SerialName("to_branch_id") val toBranchId: Long,
    @SerialName("to_branch") val toBranch: String? = null, val status: String, val note: String? = null,
    @SerialName("sent_at") val sentAt: String? = null, @SerialName("received_at") val receivedAt: String? = null,
    val items: List<StockTransferItemDto> = emptyList(),
)

@Serializable
data class StocktakeItemDto(
    val id: Long, @SerialName("product_id") val productId: Long, @SerialName("product_name") val productName: String? = null,
    val barcode: String? = null, @SerialName("system_qty") val systemQty: Int, @SerialName("counted_qty") val countedQty: Int? = null,
    val difference: Int? = null, val note: String? = null,
)

@Serializable
data class StocktakeDto(
    val id: Long, val number: String, @SerialName("branch_id") val branchId: Long,
    @SerialName("branch_name") val branchName: String? = null, val scope: String, @SerialName("category_id") val categoryId: Long? = null,
    val status: String, @SerialName("snapshot_at") val snapshotAt: String? = null, val note: String? = null,
    val items: List<StocktakeItemDto> = emptyList(),
)

@Serializable
data class StocktakeInputDto(
    @SerialName("branch_id") val branchId: Long, val scope: String = "all", @SerialName("category_id") val categoryId: Long? = null,
    val note: String? = null, @SerialName("idempotency_key") val idempotencyKey: String,
)

@Serializable
data class StocktakeCountInputDto(@SerialName("counted_qty") val countedQty: Int, val note: String? = null)

@Serializable
data class StocktakeCountResponseDto(val id: Long, @SerialName("counted_qty") val countedQty: Int, @SerialName("counted_at") val countedAt: String? = null)

@Serializable
data class StocktakeCancelInputDto(val reason: String)

@Serializable
data class UsedPhoneDto(
    val id: Long, val brand: String, val model: String, val imei: String? = null,
    @SerialName("serial_number") val serialNumber: String? = null, val condition: String,
    @SerialName("purchase_price") val purchasePrice: String, @SerialName("asking_price") val askingPrice: String,
    @SerialName("purchase_date") val purchaseDate: String, @SerialName("seller_name") val sellerName: String,
    @SerialName("seller_phone") val sellerPhone: String? = null, val status: String,
    @SerialName("image_url") val imageUrl: String? = null, val notes: String? = null,
)

@Serializable
data class UsedPhoneInputDto(
    val brand: String, val model: String, val imei: String? = null,
    @SerialName("serial_number") val serialNumber: String? = null, val condition: String,
    @SerialName("purchase_price") val purchasePrice: String, @SerialName("asking_price") val askingPrice: String,
    @SerialName("purchase_date") val purchaseDate: String, @SerialName("seller_name") val sellerName: String,
    @SerialName("seller_phone") val sellerPhone: String? = null, val status: String,
    val notes: String? = null, @SerialName("remove_image") val removeImage: Boolean = false,
)

@Serializable
data class InstallationAppointmentDto(
    val id: Long, @SerialName("customer_name") val customerName: String, val phone: String,
    @SerialName("device_model") val deviceModel: String, @SerialName("service_type") val serviceType: String,
    @SerialName("service_details") val serviceDetails: String? = null, @SerialName("scheduled_at") val scheduledAt: String,
    @SerialName("duration_minutes") val durationMinutes: Int = 60, @SerialName("technician_id") val technicianId: Long? = null,
    @SerialName("technician_name") val technicianName: String? = null, val status: String, val address: String? = null,
    val notes: String? = null, @SerialName("completed_at") val completedAt: String? = null,
)

@Serializable
data class InstallationAppointmentInputDto(
    @SerialName("customer_name") val customerName: String, val phone: String, @SerialName("device_model") val deviceModel: String,
    @SerialName("service_type") val serviceType: String, @SerialName("service_details") val serviceDetails: String? = null,
    @SerialName("scheduled_at") val scheduledAt: String, @SerialName("duration_minutes") val durationMinutes: Int,
    @SerialName("technician_id") val technicianId: Long? = null, val status: String,
    val address: String? = null, val notes: String? = null,
)

@Serializable
data class StockAdjustmentResponseDto(val data: StockMovementDto, @SerialName("product_stock") val productStock: Int)

@Serializable
data class CashierCheckoutItemDto(
    @SerialName("product_id") val productId: Long,
    val quantity: Int,
)

@Serializable
data class CashierCheckoutInputDto(
    val customer: String? = null,
    val items: List<CashierCheckoutItemDto>,
    val received: String? = null,
)

@Serializable
data class CashierCheckoutResponseDto(
    @SerialName("invoice_id") val invoiceId: Long,
    @SerialName("invoice_number") val invoiceNumber: String,
    val total: String,
    val paid: String,
    val remaining: String,
    val change: String,
)

@Serializable
data class PayAmountInputDto(val amount: String? = null, val notes: String? = null)

@Serializable
data class InstallmentStatusInputDto(val status: String)

@Serializable
data class PersonalDebtDto(
    val id: Long,
    val creditor: String,
    val description: String? = null,
    @SerialName("total_amount") val totalAmount: String,
    @SerialName("paid_amount") val paidAmount: String,
    val remaining: String,
    @SerialName("due_date") val dueDate: String? = null,
    val status: String,
    val notes: String? = null,
    val payments: List<PersonalDebtPaymentDto> = emptyList(),
)

@Serializable
data class PersonalDebtPaymentDto(
    val id: Long,
    val amount: String,
    @SerialName("paid_on") val paidOn: String? = null,
    val notes: String? = null,
)

@Serializable
data class PersonalDebtInputDto(
    val creditor: String,
    val description: String? = null,
    @SerialName("total_amount") val totalAmount: String,
    @SerialName("paid_amount") val paidAmount: String? = null,
    @SerialName("due_date") val dueDate: String? = null,
    val notes: String? = null,
)

@Serializable
data class WalletSummaryDto(
    @SerialName("wallets_total") val walletsTotal: String,
    val cash: String,
    @SerialName("profit_today") val profitToday: String,
    @SerialName("profit_available") val profitAvailable: String,
    val receivables: String,
    val capital: String,
)

@Serializable
data class WalletDto(
    val id: Long,
    val name: String,
    val provider: String,
    val identifier: String? = null,
    val balance: String,
    @SerialName("is_active") val isActive: Boolean,
    @SerialName("warn_at_percent") val warnAtPercent: Int,
    val limits: List<WalletLimitDto> = emptyList(),
)

@Serializable
data class WalletInputDto(
    val name: String,
    val provider: String,
    val identifier: String? = null,
    @SerialName("opening_balance") val openingBalance: String,
)

@Serializable
data class WalletAdjustmentInputDto(
    val amount: String,
    val reason: String,
)

@Serializable
data class WalletActionDto(
    val id: Long,
    @SerialName("is_active") val isActive: Boolean? = null,
)

@Serializable
data class WalletLimitDto(
    val label: String,
    val used: String,
    val limit: String? = null,
    val remaining: String? = null,
    val percent: Int? = null,
    val level: String,
)

@Serializable
data class InstallmentDto(
    val id: Long,
    @SerialName("invoice_id") val invoiceId: Long? = null,
    @SerialName("customer_id") val customerId: Long? = null,
    val customer: String,
    @SerialName("product_id") val productId: Long? = null,
    @SerialName("product_name") val productName: String,
    @SerialName("product_price") val productPrice: String,
    @SerialName("payment_date") val paymentDate: String? = null,
    @SerialName("next_payment_date") val nextPaymentDate: String? = null,
    @SerialName("paid_amount") val paidAmount: String,
    val remaining: String,
    val quantity: Int,
    val status: String,
    val items: List<InvoiceItemDto> = emptyList(),
)

@Serializable
data class InvoiceDto(
    val id: Long,
    @SerialName("invoice_number") val invoiceNumber: String,
    val customer: String,
    @SerialName("invoice_date") val invoiceDate: String,
    val status: String,
    val quantity: Int,
    @SerialName("total_amount") val totalAmount: String,
    @SerialName("paid_amount") val paidAmount: String,
    @SerialName("remaining_amount") val remainingAmount: String,
    val items: List<InvoiceItemDto> = emptyList(),
    val notes: String? = null,
    @SerialName("created_at") val createdAt: String? = null,
)

@Serializable
data class InvoiceItemDto(
    @SerialName("product_id") val productId: Long? = null,
    val name: String? = null,
    val price: String? = null,
    val quantity: Int? = null,
    @SerialName("line_total") val lineTotal: String? = null,
)

@Serializable
data class LoginRequestDto(
    val username: String,
    val password: String,
    @SerialName("device_name") val deviceName: String,
)

@Serializable
data class CustomerInputDto(
    val name: String,
    val phone: String? = null,
    val address: String? = null,
)

@Serializable
data class CategoryInputDto(
    val name: String,
)

@Serializable
data class InvoiceInputDto(
    val customer: String,
    @SerialName("product_ids") val productIds: List<Long>,
    val quantities: List<Int>,
    @SerialName("invoice_date") val invoiceDate: String,
    @SerialName("paid_amount") val paidAmount: String,
    val notes: String? = null,
)

@Serializable
data class InstallmentInputDto(
    val customer: String,
    @SerialName("product_ids") val productIds: List<Long>? = null,
    val quantities: List<Int>? = null,
    @SerialName("product_name") val productName: String? = null,
    @SerialName("total_amount") val totalAmount: String? = null,
    @SerialName("payment_date") val paymentDate: String? = null,
    @SerialName("next_payment_date") val nextPaymentDate: String? = null,
    @SerialName("paid_amount") val paidAmount: String,
    val notes: String? = null,
)
