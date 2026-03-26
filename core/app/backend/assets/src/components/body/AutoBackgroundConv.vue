<template>
	<div class="w-full flex md:flex-row flex-col justify-between items-center p-4 border-b" :class="backgroundConv !== 'off' ? '!border-b-0' : ''">
		<label class="w-full md:w-1/2 flex justify-start mb-2 md:mb-0" for="cronjobdirectory">Automatic image Processing in
			Background</label>
		<div class="w-full md:w-1/2 flex justify-start md:justify-end">
			<select id="cronjobdirectory" class="w-full md:w-auto" v-model="backgroundConv" v-on:change="setBackgroundConv">
				<option value="off">Inactive</option>
				<option value="theme">Theme Directory</option>
				<option value="upload">Upload Directory</option>
				<option value="themeandupload">Theme & Upload Directory</option>
			</select>
		</div>
	</div>
	<div class="w-full flex flex-col items-start p-4 pt-0 border-b" v-if="backgroundConv !== 'off'">
		<div class=" bg-gray-50 rounded  w-full">
			<TimeSelect/>
		</div>
		<div class="w-full mt-3 space-y-3">
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgWorkerLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-24" type="number" min="1" max="2" v-model.number="bgWorkerCount" v-on:change="setBgWorkerCount" />
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgBatchSizeLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-24" type="number" min="10" max="25" v-model.number="bgBatchSize" v-on:change="setBgRunBatchSize" />
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgSleepLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<select class="w-full md:w-auto" v-model.number="bgSleepSeconds" v-on:change="setBgSleepSeconds">
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
						<input type="checkbox" class="sr-only peer" v-model="bgIdleAware" v-on:change="setBgIdleAware"/>
						<div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600"></div>
						<div class="ml-3">{{ bgIdleAware ? t('yes') : t('no') }}</div>
					</label>
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgActiveUsersLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-24" type="number" min="1" max="20" v-model.number="bgActiveUsers" v-on:change="setBgActiveUsers" />
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgActivityWindowLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<input class="border border-gray-300 rounded px-3 py-1 w-32" type="number" min="15" step="15" v-model.number="bgActivityWindowSeconds" v-on:change="setBgActivityWindowSeconds" />
					<span class="ml-2 text-sm text-gray-500">{{ t('secondsLabel') }}</span>
				</div>
			</div>
			<div class="w-full flex flex-row justify-between items-center p-4 border-b bg-white rounded">
				<label class="w-1/2 flex justify-start">{{ t('bgQuietWindowLabel') }}</label>
				<div class="w-1/2 flex justify-end items-center">
					<label class="inline-flex items-center cursor-pointer">
						<input type="checkbox" class="sr-only peer" v-model="bgQuietWindowEnabled" v-on:change="setBgQuietWindowEnabled"/>
						<div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600"></div>
						<div class="ml-3">{{ bgQuietWindowEnabled ? t('yes') : t('no') }}</div>
					</label>
				</div>
			</div>
			<div class="w-full flex md:flex-row flex-col justify-between items-center p-4 bg-white rounded">
				<label class="w-full md:w-1/2 flex justify-start">{{ t('bgQuietWindowStartLabel') }}</label>
				<div class="w-full md:w-1/2 flex justify-end items-center mt-2 md:mt-0">
					<input class="border border-gray-300 rounded px-3 py-1 w-28" type="time" v-model="bgQuietWindowStart" v-on:change="setBgQuietWindowStart" />
				</div>
			</div>
			<div class="w-full flex md:flex-row flex-col justify-between items-center p-4 bg-white rounded">
				<label class="w-full md:w-1/2 flex justify-start">{{ t('bgQuietWindowEndLabel') }}</label>
				<div class="w-full md:w-1/2 flex justify-end items-center mt-2 md:mt-0">
					<input class="border border-gray-300 rounded px-3 py-1 w-28" type="time" v-model="bgQuietWindowEnd" v-on:change="setBgQuietWindowEnd" />
				</div>
			</div>
		</div>
	</div>
</template>
<script setup>
import { ref } from 'vue';
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
import { useI18n } from 'vue-i18n';
import TimeSelect from './autobackgroundconv/TimeSelect.vue';
const { t } = useI18n({});

const getBackgroundConv = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBackgroudConv');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			backgroundConv.value = res

		})
		.catch(err => console.log(err));
}
getBackgroundConv();

const setBackgroundConv = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBackgroudConv');
	data.append('avifbackgroundConv', backgroundConv.value);
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {

		})
		.catch(err => console.log(err));
}

const getBgWorkerCount = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgWorkerCount');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgWorkerCount.value = Number(res || 1);
		})
		.catch(err => console.log(err));
}
const setBgWorkerCount = () => {
	const value = Math.max(1, Math.min(2, parseInt(bgWorkerCount.value || 1, 10)));
	bgWorkerCount.value = value;
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgWorkerCount');
	data.append('avifbgworkercount', value);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgRunBatchSize = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgRunBatchSize');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgBatchSize.value = Number(res || 20);
		})
		.catch(err => console.log(err));
}
const setBgRunBatchSize = () => {
	const value = Math.max(10, Math.min(25, parseInt(bgBatchSize.value || 20, 10)));
	bgBatchSize.value = value;
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgRunBatchSize');
	data.append('avifbgrunbatchsize', value);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgSleepSeconds = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgSleepSeconds');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgSleepSeconds.value = Number(res || 1);
		})
		.catch(err => console.log(err));
}
const setBgSleepSeconds = () => {
	const value = Math.max(0, Math.min(2, parseInt(bgSleepSeconds.value || 1, 10)));
	bgSleepSeconds.value = value;
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgSleepSeconds');
	data.append('avifbgsleepseconds', value);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgIdleAware = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgIdleAware');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgIdleAware.value = Boolean(res);
		})
		.catch(err => console.log(err));
}
const setBgIdleAware = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgIdleAware');
	data.append('avifbgidleaware', bgIdleAware.value ? 1 : 0);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgActiveUsers = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgActiveUsers');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgActiveUsers.value = Number(res || 3);
		})
		.catch(err => console.log(err));
}
const setBgActiveUsers = () => {
	const value = Math.max(1, Math.floor(parseInt(bgActiveUsers.value || 3, 10)));
	bgActiveUsers.value = value;
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgActiveUsers');
	data.append('avifbgactiveusers', value);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgActivityWindowSeconds = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgActivityWindowSeconds');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgActivityWindowSeconds.value = Number(res || 60);
		})
		.catch(err => console.log(err));
}
const setBgActivityWindowSeconds = () => {
	const value = Math.max(15, parseInt(bgActivityWindowSeconds.value || 60, 10));
	bgActivityWindowSeconds.value = value;
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgActivityWindowSeconds');
	data.append('avifbgactivitywindowseconds', value);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgQuietWindowEnabled = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgQuietWindowEnabled');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgQuietWindowEnabled.value = Boolean(res);
		})
		.catch(err => console.log(err));
}
const setBgQuietWindowEnabled = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgQuietWindowEnabled');
	data.append('avifbgquietwindowenabled', bgQuietWindowEnabled.value ? 1 : 0);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgQuietWindowStart = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgQuietWindowStart');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgQuietWindowStart.value = res || '01:00';
		})
		.catch(err => console.log(err));
}
const setBgQuietWindowStart = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgQuietWindowStart');
	data.append('avifbgquietwindowstart', bgQuietWindowStart.value);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const getBgQuietWindowEnd = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxGetBgQuietWindowEnd');
	fetch(avife_ajax_path, {
		method: 'POST',
		credentials: 'same-origin',
		body: data
	})
		.then(res => res.json())
		.then(res => {
			bgQuietWindowEnd.value = res || '06:00';
		})
		.catch(err => console.log(err));
}
const setBgQuietWindowEnd = () => {
	const data = new FormData();
	data.append('avife_nonce', avife_nonce);
	data.append('action', 'ajaxSetBgQuietWindowEnd');
	data.append('avifbgquietwindowend', bgQuietWindowEnd.value);
	fetch(avife_ajax_path, { method: 'POST', credentials: 'same-origin', body: data })
		.then(res => res.json())
		.then(() => {})
		.catch(err => console.log(err));
}

const loadBgSettings = () => {
	getBgWorkerCount();
	getBgRunBatchSize();
	getBgSleepSeconds();
	getBgIdleAware();
	getBgActiveUsers();
	getBgActivityWindowSeconds();
	getBgQuietWindowEnabled();
	getBgQuietWindowStart();
	getBgQuietWindowEnd();
}

loadBgSettings();
</script>
