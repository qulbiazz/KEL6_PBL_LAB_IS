<!-- Visitor Statistics Widget -->
<div class="bg-gray-700 rounded-lg p-6 shadow-lg">
    <h4 class="text-xl font-bold mb-4 text-center text-white">
        <i class="fas fa-globe-americas mr-2"></i>Visitor Statistics
    </h4>
    
    <!-- Main Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="text-center">
            <div class="text-3xl font-bold text-blue-400">
                <?php echo number_format($visitorStats['total'] ?? 0); ?>
            </div>
            <div class="text-xs text-gray-400 mt-1">Total Visitors</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-green-400">
                <?php echo number_format($visitorStats['today'] ?? 0); ?>
            </div>
            <div class="text-xs text-gray-400 mt-1">Today</div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-yellow-400">
                <?php echo number_format($visitorStats['online'] ?? 0); ?>
            </div>
            <div class="text-xs text-gray-400 mt-1">Online Now</div>
        </div>
    </div>
    
    <!-- Page Views -->
    <div class="text-center mb-4 pb-4 border-b border-gray-600">
        <div class="text-sm text-gray-400">
            <i class="fas fa-eye mr-1"></i>
            Total Page Views: <span class="font-semibold text-white"><?php echo number_format($visitorStats['total_views'] ?? 0); ?></span>
        </div>
    </div>
    
    <!-- Countries -->
    <?php if (!empty($visitorStats['countries'])): ?>
    <div>
        <h5 class="text-sm font-semibold text-gray-300 mb-3 text-center">Visitors by Country</h5>
        <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
            <?php foreach ($visitorStats['countries'] as $country): ?>
            <div class="flex items-center justify-between bg-gray-800 rounded px-3 py-2 hover:bg-gray-750 transition">
                <div class="flex items-center space-x-2 flex-1">
                    <span class="text-2xl">
                        <?php echo VisitorModel::getCountryFlag($country['country_code']); ?>
                    </span>
                    <span class="text-sm text-gray-300 truncate">
                        <?php echo htmlspecialchars($country['country']); ?>
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-xs font-semibold text-blue-400">
                        <?php echo number_format($country['count']); ?>
                    </div>
                    <div class="w-16 bg-gray-700 rounded-full h-2">
                        <?php 
                        $maxCount = $visitorStats['countries'][0]['count'] ?? 1;
                        $percentage = ($country['count'] / $maxCount) * 100;
                        ?>
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" 
                             style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Custom scrollbar styles for country list -->
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #374151;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4B5563;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #6B7280;
}
</style>
