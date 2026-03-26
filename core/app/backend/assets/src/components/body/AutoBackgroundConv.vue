<template>
	<div class="w-full flex md:flex-row flex-col justify-between items-center p-4 border-b" :class="backgroundConv !== 'off' ? '!border-b-0' : ''">
		<label class="w-full md:w-1/2 flex justify-start mb-2 md:mb-0" for="cronjobdirectory">Automatic image Processing in
			Background</label>
		<div class="w-full md:w-1/2 flex justify-start md:justify-end">
			<select id="cronjobdirectory" class="w-full md:w-auto" v-model="backgroundConv" @change="setBackgroundConv">
				<option value="off">Inactive</option>
				<option value="theme">Theme Directory</option>
				<option value="upload">Upload Directory</option>
				<option value="themeandupload">Theme & Upload Directory</option>
			</select>
		</div>
	</div>
	<div class="w-full flex flex-col items-start p-4 pt-0 border-b" v-if="backgroundConv !== 'off'">
		<div class="bg-gray-50 rounded w-full">
			<TimeSelect/>
		</div>
		<div class="w-full mt-3 p-4 rounded bg-slate-900 text-white">
			<div class="flex md:flex-row flex-col md:items-center md:justify-between gap-2">
				<div>
					<div class="text-xs uppercase tracking-wide text-slate-300">{{ t('bgStatusTitle') }}</div>
					<div class="text-lg font-semibold">{{ statusHeadline }}</div>
				</div>
				<div class="flex gap-2">
					<button class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-500 transition" type="button" @click="loadProcessingStatus">
						{{ t('refreshStatusLabel') }}
					</button>
					<button class="px-3 py-2 rounded bg-rose-600 hover:bg-rose-500 transition" type="button" @click="killBgWorkers">
						{{ t('killWorkersLabel') }}
					</button>
				</div>
			</div>
			<div class="mt-2 text-sm text-slate-200">{{ statusReason }}</div>
			<div class="mt-2 text-sm text-amber-200" v-if="lastKillMessage">{{ lastKillMessage }}</div>
			<div class="mt-3 grid md:grid-cols-2 gap-3 text-sm">
				<div class="rounded bg-slate-800/80 p-3">
					<div class="text-slate-300">{{ t('bgCurrentTimeLabel') }}</div>
					<div class="font-medium">{{ processingStatus.currentTimeLocal || '-' }}</div>
				</div>
				<div class="rounded bg-slate-800/80 p-3">
					<div class="text-slate-300">{{ t('bgNextRunLabel') }}</div>
					<div class="font-medium">{{ processingStatus.nextRunLocal || t('bgNoScheduledRunLabel') }}</div>
				</div>
				<div class="rounded bg-slate-800/80 p-3">
					<div class="text-slate-300">{{ t('bgActiveUsersStatusLabel') }}</div>
					<div class="font-medium">{{ processingStatus.activeUsers ?? 0 }} / {{ processingStatus.activeUsersThreshold ?? bgActiveUsers }}</div>
				</div>
				<div class="rounded bg-slate-800/80 p-3">
					<div class="text-slate-300">{{ t('bgCronStatusLabel') }}</div>
					<div class="font-medium">{{ processingStatus.cronScheduled ? t('yes') : t('no') }}</div>
				</div>
			</div>
		</div>
		<div class="w-full mt-3 space-y-3">
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgWorkerLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-24" type="number" min="1" max="2" v-model.number="bgWorkerCount" @change="setBgWorkerCount" />
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgBatchSizeLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-24" type="number" min="10" max="25" v-model.number="bgBatchSize" @change="setBgRunBatchSize" />
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgSleepLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<select class="w-full md:w-auto" v-model.number="bgSleepSeconds" @change="setBgSleepSeconds">
						<option value="0">0s</option>
						<option value="1">1s</option>
						<option value="2">2s</option>
					</select>
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgIdleAwareLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<label class="inline-flex items-center cursor-pointer">
						<input type="checkbox" class="sr-only peer" v-model="bgIdleAware" @change="setBgIdleAware"/>
						<div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600"></div>
						<div class="ml-3">{{ bgIdleAware ? t('yes') : t('no') }}</div>
					</label>
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgActiveUsersLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-24" type="number" min="1" max="20" v-model.number="bgActiveUsers" @change="setBgActiveUsers" />
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgActivityWindowLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-32" type="number" min="15" step="15" v-model.number="bgActivityWindowSeconds" @change="setBgActivityWindowSeconds" />
					<span class="ml-2 text-sm text-gray-500">{{ t('secondsLabel') }}</span>
				</div>
			</div>
			<div class="w-full p-4 bg-white rounded border-b">
				<div class="flex md:flex-row flex-col md:items-center md:justify-between gap-3">
					<div>
						<div class="font-medium">{{ t('bgQuietWindowLabel') }}</div>
						<div class="text-sm text-gray-500">{{ t('bgQuietWindowDescription') }}</div>
					</div>
					<label class="inline-flex items-center cursor-pointer">
						<input type="checkbox" class="sr-only peer" v-model="bgQuietWindowEnabled" @change="setBgQuietWindowEnabled"/>
						<div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600"></div>
						<div class="ml-3">{{ bgQuietWindowEnabled ? t('yes') : t('no') }}</div>
					</label>
				</div>
				<div class="grid md:grid-cols-2 gap-3 mt-3">
					<div class="flex md:flex-row flex-col justify-between items-center p-3 bg-gray-50 rounded">
						<label class="w-full md:w-1/2 flex justify-start">{{ t('bgQuietWindowStartLabel') }}</label>
						<div class="w-full md:w-1/2 flex justify-end items-center mt-2 md:mt-0">
							<input class="border border-gray-300 rounded px-3 py-1 w-28" type="time" v-model="bgQuietWindowStart" @change="setBgQuietWindowStart" />
						</div>
					</div>
					<div class="flex md:flex-row flex-col justify-between items-center p-3 bg-gray-50 rounded">
						<label class="w-full md:w-1/2 flex justify-start">{{ t('bgQuietWindowEndLabel') }}</label>
						<div class="w-full md:w-1/2 flex justify-end items-center mt-2 md:mt-0">
							<input class="border border-gray-300 rounded px-3 py-1 w-28" type="time" v-model="bgQuietWindowEnd" @change="setBgQuietWindowEnd" />
						</div>
					</div>
				</div>
			</div>
			<div class="w-full p-4 bg-white rounded">
				<div class="flex md:flex-row flex-col md:items-center md:justify-between gap-3">
					<div>
						<div class="font-medium">{{ t('bgNoProcessingWindowLabel') }}</div>
						<div class="text-sm text-gray-500">{{ t('bgNoProcessingWindowDescription') }}</div>
					</div>
					<label class="inline-flex items-center cursor-pointer">
						<input type="checkbox" class="sr-only peer" v-model="bgNoProcessingWindowEnabled" @change="setBgNoProcessingWindowEnabled"/>
						<div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600"></div>
						<div class="ml-3">{{ bgNoProcessingWindowEnabled ? t('yes') : t('no') }}</div>
					</label>
				</div>
				<div class="grid md:grid-cols-2 gap-3 mt-3">
					<div class="flex md:flex-row flex-col justify-between items-center p-3 bg-gray-50 rounded">
						<label class="w-full md:w-1/2 flex justify-start">{{ t('bgNoProcessingWindowStartLabel') }}</label>
						<div class="w-full md:w-1/2 flex justify-end items-center mt-2 md:mt-0">
							<input class="border border-gray-300 rounded px-3 py-1 w-28" type="time" v-model="bgNoProcessingWindowStart" @change="setBgNoProcessingWindowStart" />
						</div>
					</div>
					<div class="flex md:flex-row flex-col justify-between items-center p-3 bg-gray-50 rounded">
						<label class="w-full md:w-1/2 flex justify-start">{{ t('bgNoProcessingWindowEndLabel') }}</label>
						<div class="w-full md:w-1/2 flex justify-end items-center mt-2 md:mt-0">
							<input class="border border-gray-300 rounded px-3 py-1 w-28" type="time" v-model="bgNoProcessingWindowEnd" @change="setBgNoProcessingWindowEnd" />
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TimeSelect from './autobackgroundconv/TimeSelect.vue';

const { t } = useI18n({});

const backgroundConv = ref('off');
const bgWorkerCount = ref(1);
const bgBatchSize = ref(20);
const bgSleepSeconds = ref(1);
const bgIdleAware = ref(false);
const bgActiveUsers = ref(3);
const bgActivityWindowSeconds = ref(60);
const bgQuietWindowEnabled = ref(false);
const bgQuietWindowStart = ref('01:00');
const bgQuietWindowEnd = ref('06:00');
const bgNoProcessingWindowEnabled = ref(false);
const bgNoProcessingWindowStart = ref('22:00');
const bgNoProcessingWindowEnd = ref('08:00');
const processingStatus = ref({});
const lastKillMessage = ref('');

let statusRefreshTimer = null;

const statusHeadline = computed(() => {
	if (processingStatus.value.statusLabel === 'ready') {
		return t('bgStatusReady');
	}

	return t('bgStatusPaused');
});

const statusReason = computed(() => {
	const reason = processingStatus.value.reason || 'ready';
	const labels = {
		ready: t('bgReasonReady'),
		background_disabled: t('bgReasonDisabled'),
		no_processing_hours: t('bgReasonNoProcessingHours'),
		outside_off_peak_hours: t('bgReasonOutsideOffPeak'),
		site_busy: t('bgReasonSiteBusy'),
	};

	return labels[reason] || reason;
});

const fetchJson = (action, extraData = {}) => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', action);

	Object.entries(extraData).forEach(([key, value]) => {
		data.append(key, value);
	});

	return fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data,
	}).then((res) => res.json());
};

const clampInt = (value, min, max = null) => {
	const parsed = parseInt(value, 10);
	const safeValue = Number.isNaN(parsed) ? min : parsed;
	if (max === null) {
		return Math.max(min, safeValue);
	}

	return Math.max(min, Math.min(max, safeValue));
};

const setBooleanSetting = (action, field, value) => {
	return fetchJson(action, {
		[field]: value ? 1 : 0,
	}).then(() => {
		loadProcessingStatus();
	}).catch((err) => console.log(err));
};

const setNumericSetting = (action, field, value, updateRef) => {
	updateRef.value = value;
	return fetchJson(action, {
		[field]: value,
	}).then(() => {
		loadProcessingStatus();
	}).catch((err) => console.log(err));
};

const setTimeSetting = (action, field, value, updateRef) => {
	updateRef.value = value;
	return fetchJson(action, {
		[field]: value,
	}).then(() => {
		loadProcessingStatus();
	}).catch((err) => console.log(err));
};

const getBackgroundConv = () => {
	fetchJson('ajaxGetBackgroudConv')
		.then((res) => {
			backgroundConv.value = res;
		})
		.catch((err) => console.log(err));
};

const setBackgroundConv = () => {
	fetchJson('ajaxSetBackgroudConv', {
		avifbackgroundConv: backgroundConv.value,
	}).then(() => {
		loadProcessingStatus();
	}).catch((err) => console.log(err));
};

const getBgWorkerCount = () => {
	fetchJson('ajaxGetBgWorkerCount')
		.then((res) => {
			bgWorkerCount.value = Number(res || 1);
		})
		.catch((err) => console.log(err));
};

const setBgWorkerCount = () => {
	const value = clampInt(bgWorkerCount.value, 1, 2);
	setNumericSetting('ajaxSetBgWorkerCount', 'avifbgworkercount', value, bgWorkerCount);
};

const getBgRunBatchSize = () => {
	fetchJson('ajaxGetBgRunBatchSize')
		.then((res) => {
			bgBatchSize.value = Number(res || 20);
		})
		.catch((err) => console.log(err));
};

const setBgRunBatchSize = () => {
	const value = clampInt(bgBatchSize.value, 10, 25);
	setNumericSetting('ajaxSetBgRunBatchSize', 'avifbgrunbatchsize', value, bgBatchSize);
};

const getBgSleepSeconds = () => {
	fetchJson('ajaxGetBgSleepSeconds')
		.then((res) => {
			bgSleepSeconds.value = Number(res || 1);
		})
		.catch((err) => console.log(err));
};

const setBgSleepSeconds = () => {
	const value = clampInt(bgSleepSeconds.value, 0, 2);
	setNumericSetting('ajaxSetBgSleepSeconds', 'avifbgsleepseconds', value, bgSleepSeconds);
};

const getBgIdleAware = () => {
	fetchJson('ajaxGetBgIdleAware')
		.then((res) => {
			bgIdleAware.value = Boolean(res);
		})
		.catch((err) => console.log(err));
};

const setBgIdleAware = () => {
	setBooleanSetting('ajaxSetBgIdleAware', 'avifbgidleaware', bgIdleAware.value);
};

const getBgActiveUsers = () => {
	fetchJson('ajaxGetBgActiveUsers')
		.then((res) => {
			bgActiveUsers.value = Number(res || 3);
		})
		.catch((err) => console.log(err));
};

const setBgActiveUsers = () => {
	const value = clampInt(bgActiveUsers.value, 1, 20);
	setNumericSetting('ajaxSetBgActiveUsers', 'avifbgactiveusers', value, bgActiveUsers);
};

const getBgActivityWindowSeconds = () => {
	fetchJson('ajaxGetBgActivityWindowSeconds')
		.then((res) => {
			bgActivityWindowSeconds.value = Number(res || 60);
		})
		.catch((err) => console.log(err));
};

const setBgActivityWindowSeconds = () => {
	const value = clampInt(bgActivityWindowSeconds.value, 15);
	setNumericSetting('ajaxSetBgActivityWindowSeconds', 'avifbgactivitywindowseconds', value, bgActivityWindowSeconds);
};

const getBgQuietWindowEnabled = () => {
	fetchJson('ajaxGetBgQuietWindowEnabled')
		.then((res) => {
			bgQuietWindowEnabled.value = Boolean(res);
		})
		.catch((err) => console.log(err));
};

const setBgQuietWindowEnabled = () => {
	setBooleanSetting('ajaxSetBgQuietWindowEnabled', 'avifbgquietwindowenabled', bgQuietWindowEnabled.value);
};

const getBgQuietWindowStart = () => {
	fetchJson('ajaxGetBgQuietWindowStart')
		.then((res) => {
			bgQuietWindowStart.value = res || '01:00';
		})
		.catch((err) => console.log(err));
};

const setBgQuietWindowStart = () => {
	setTimeSetting('ajaxSetBgQuietWindowStart', 'avifbgquietwindowstart', bgQuietWindowStart.value, bgQuietWindowStart);
};

const getBgQuietWindowEnd = () => {
	fetchJson('ajaxGetBgQuietWindowEnd')
		.then((res) => {
			bgQuietWindowEnd.value = res || '06:00';
		})
		.catch((err) => console.log(err));
};

const setBgQuietWindowEnd = () => {
	setTimeSetting('ajaxSetBgQuietWindowEnd', 'avifbgquietwindowend', bgQuietWindowEnd.value, bgQuietWindowEnd);
};

const getBgNoProcessingWindowEnabled = () => {
	fetchJson('ajaxGetBgNoProcessingWindowEnabled')
		.then((res) => {
			bgNoProcessingWindowEnabled.value = Boolean(res);
		})
		.catch((err) => console.log(err));
};

const setBgNoProcessingWindowEnabled = () => {
	setBooleanSetting('ajaxSetBgNoProcessingWindowEnabled', 'avifbgnoprocessingenabled', bgNoProcessingWindowEnabled.value);
};

const getBgNoProcessingWindowStart = () => {
	fetchJson('ajaxGetBgNoProcessingWindowStart')
		.then((res) => {
			bgNoProcessingWindowStart.value = res || '22:00';
		})
		.catch((err) => console.log(err));
};

const setBgNoProcessingWindowStart = () => {
	setTimeSetting('ajaxSetBgNoProcessingWindowStart', 'avifbgnoprocessingstart', bgNoProcessingWindowStart.value, bgNoProcessingWindowStart);
};

const getBgNoProcessingWindowEnd = () => {
	fetchJson('ajaxGetBgNoProcessingWindowEnd')
		.then((res) => {
			bgNoProcessingWindowEnd.value = res || '08:00';
		})
		.catch((err) => console.log(err));
};

const setBgNoProcessingWindowEnd = () => {
	setTimeSetting('ajaxSetBgNoProcessingWindowEnd', 'avifbgnoprocessingend', bgNoProcessingWindowEnd.value, bgNoProcessingWindowEnd);
};

const loadProcessingStatus = () => {
	fetchJson('ajaxGetBgProcessingStatus')
		.then((res) => {
			processingStatus.value = res || {};
		})
		.catch((err) => console.log(err));
};

const killBgWorkers = () => {
	fetchJson('ajaxKillBgWorkers')
		.then((res) => {
			const killed = Number(res?.workersKilled || 0);
			lastKillMessage.value = t('killWorkersResultLabel', { count: killed });
			processingStatus.value = res?.status || {};
		})
		.catch((err) => console.log(err));
};

const loadBgSettings = () => {
	getBackgroundConv();
	getBgWorkerCount();
	getBgRunBatchSize();
	getBgSleepSeconds();
	getBgIdleAware();
	getBgActiveUsers();
	getBgActivityWindowSeconds();
	getBgQuietWindowEnabled();
	getBgQuietWindowStart();
	getBgQuietWindowEnd();
	getBgNoProcessingWindowEnabled();
	getBgNoProcessingWindowStart();
	getBgNoProcessingWindowEnd();
	loadProcessingStatus();
};

onMounted(() => {
	loadBgSettings();
	statusRefreshTimer = window.setInterval(loadProcessingStatus, 30000);
});

onBeforeUnmount(() => {
	if (statusRefreshTimer) {
		window.clearInterval(statusRefreshTimer);
	}
});
</script>
