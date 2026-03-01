<!-- Staff Tabs - Reusable pill bar for team trackers -->
<div class="sp-staff-bar" x-show="staffList.length > 1">
    <button @click="staffFilter = ''; loadData(1)"
            class="sp-staff-pill" :class="staffFilter === '' && 'on'">All</button>
    <template x-for="staff in staffList" :key="staff.id">
        <button @click="staffFilter = staff.id.toString(); loadData(1)"
                class="sp-staff-pill" :class="staffFilter === staff.id.toString() && 'on'"
                x-text="staff.display_name || staff.full_name"></button>
    </template>
</div>
