<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header con gradiente sutil -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V12.5m0 9.5a11.955 11.955 0 01-8.618-3.04M12 22a11.955 11.955 0 018.618-3.04M12 22V12.5m0 9.5a11.955 11.955 0 008.618-3.04M12 12.5a11.955 11.955 0 008.618-3.04"></path>
                        </svg>
                    </div>
                    Control de Acceso de Usuarios
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Gestiona restricciones de IP y horarios para garantizar la seguridad de tu empresa.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-full text-[10px] font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider border border-gray-200 dark:border-gray-600">
                    Tu IP Actual: <span class="text-indigo-600 dark:text-indigo-400">{{ $currentIp }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Columna Izquierda: Lista de Usuarios -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="relative">
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar usuario..." 
                                class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl text-sm transition-all">
                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="max-h-[600px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                        @foreach($users as $user)
                        <div 
                            wire:click="selectUser({{ $user->id }})"
                            class="p-4 flex items-center gap-3 cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $selectedUserId == $user->id ? 'bg-indigo-50/50 dark:bg-indigo-900/20 border-r-4 border-indigo-500' : '' }}">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-sm uppercase">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $user->name }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                            </div>
                            @if($user->isSuperAdmin())
                            <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-[9px] font-black rounded-md uppercase tracking-tighter">Admin Central</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Configuración -->
            <div class="lg:col-span-8">
                @if($selectedUser)
                <div x-data="{ tab: 'ips' }" class="space-y-6">
                    <!-- Tabs -->
                    <div class="flex p-1 bg-gray-200/50 dark:bg-gray-800 rounded-xl max-w-fit">
                        <button @click="tab = 'ips'" :class="tab === 'ips' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-400'" class="px-6 py-2 rounded-lg text-sm font-bold transition-all">IPs Autorizadas</button>
                        <button @click="tab = 'horarios'" :class="tab === 'horarios' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-400'" class="px-6 py-2 rounded-lg text-sm font-bold transition-all">Horarios Laborales</button>
                        <button @click="tab = 'logs'" :class="tab === 'logs' ? 'bg-white dark:bg-gray-700 shadow-sm text-indigo-600 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-400'" class="px-6 py-2 rounded-lg text-sm font-bold transition-all">Historial de Accesos</button>
                    </div>

                    <!-- Contenido Tab: IPs -->
                    <div x-show="tab === 'ips'" x-transition class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5.618 4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 018.618 3.04M12 2.944V12.5M12 2.944a11.955 11.955 0 00-8.618 3.04M12 12.5V22m0-9.5a11.955 11.955 0 01-8.618-3.04M12 22a11.955 11.955 0 018.618-3.04"></path></svg>
                                Agregar Nueva IP Autorizada
                            </h3>
                            <form wire:submit="addIp" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Dirección IP</label>
                                    <input wire:model="newIp" type="text" placeholder="Ej: 192.168.1.1" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl text-sm transition-all focus:bg-white dark:focus:bg-gray-800">
                                    @error('newIp') <span class="text-[10px] text-red-500 mt-1 block px-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Descripción (Opcional)</label>
                                    <input wire:model="ipDescription" type="text" placeholder="Ej: Casa del empleado" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl text-sm transition-all focus:bg-white dark:focus:bg-gray-800">
                                </div>
                                <div class="md:col-span-1 flex items-end">
                                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-sm transition-all shadow-md shadow-indigo-200 dark:shadow-none active:scale-95">
                                        Vincular IP
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Dirección IP</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Descripción</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Estado</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($ips as $ip)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $ip->ip_allowed }}</td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $ip->description ?: '-' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-[10px] font-black rounded-full uppercase">Activo</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button wire:click="deleteIp({{ $ip->id }})" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No hay IPs configuradas para este usuario.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Contenido Tab: Horarios -->
                    <div x-show="tab === 'horarios'" x-transition class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Configuración Rápida</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Establecer horario laboral estándar automáticamente.</p>
                            </div>
                            <button wire:click="applyStandardSchedule" class="px-5 py-2.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-bold rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all border border-indigo-200 dark:border-indigo-700">
                                Aplicar Jornada L-S (08:00 - 17:30)
                            </button>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Agregar Franja Horaria</h3>
                            <form wire:submit="addSchedule" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Día de la Semana</label>
                                    <select wire:model="dayOfWeek" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl text-sm transition-all">
                                        <option value="1">Lunes</option>
                                        <option value="2">Martes</option>
                                        <option value="3">Miércoles</option>
                                        <option value="4">Jueves</option>
                                        <option value="5">Viernes</option>
                                        <option value="6">Sábado</option>
                                        <option value="7">Domingo</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Hora Inicio</label>
                                    <input wire:model="startTime" type="time" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl text-sm transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Hora Fin</label>
                                    <input wire:model="endTime" type="time" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl text-sm transition-all">
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-xl text-sm shadow-md transition-all active:scale-95">
                                        Guardar Horario
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @php
                                $diasNombres = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                            @endphp
                            @forelse($horarios as $horario)
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between border-l-4 border-l-indigo-500">
                                <div>
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">{{ $diasNombres[$horario->day_of_week] }}</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ substr($horario->start_time, 0, 5) }} - {{ substr($horario->end_time, 0, 5) }}
                                    </p>
                                </div>
                                <button wire:click="deleteSchedule({{ $horario->id }})" class="p-2 text-red-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                            @empty
                            <div class="col-span-full py-12 text-center text-gray-400 italic">No hay horarios definidos.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Contenido Tab: Logs -->
                    <div x-show="tab === 'logs'" x-transition class="space-y-4">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Intentos de Acceso Recientes</h3>
                                <button onclick="window.location.reload()" class="text-xs text-indigo-600 font-bold hover:underline">Actualizar</button>
                            </div>
                            <div class="max-h-[500px] overflow-y-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="sticky top-0 bg-white dark:bg-gray-800 bg-opacity-95 backdrop-blur-sm shadow-sm">
                                        <tr>
                                            <th class="px-5 py-3 font-black text-gray-400 uppercase tracking-widest text-[9px]">Fecha/Hora</th>
                                            <th class="px-5 py-3 font-black text-gray-400 uppercase tracking-widest text-[9px]">IP</th>
                                            <th class="px-5 py-3 font-black text-gray-400 uppercase tracking-widest text-[9px]">Resultado</th>
                                            <th class="px-5 py-3 font-black text-gray-400 uppercase tracking-widest text-[9px]">Navegador</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($logs as $log)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                            <td class="px-5 py-3 font-mono font-bold">{{ $log->ip_address }}</td>
                                            <td class="px-5 py-3">
                                                @if($log->access_type == 'exitoso')
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-[9px] font-black uppercase tracking-tighter">Éxito</span>
                                                @elseif($log->access_type == 'ip_denegada')
                                                    <span class="px-2 py-0.5 bg-red-100 text-red-600 rounded text-[9px] font-black uppercase tracking-tighter">IP Bloqueada</span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-600 rounded text-[9px] font-black uppercase tracking-tighter">Horario Bloqueado</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-gray-400 max-w-xs truncate" title="{{ $log->user_agent }}">{{ $log->user_agent }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <!-- Estado Vacío -->
                <div class="h-full min-h-[500px] flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800/30 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-3xl p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Selecciona un Usuario</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed">
                        Elige un miembro de tu equipo de la lista izquierda para comenzar a configurar sus restricciones de acceso.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal', (data) => {
                Swal.fire({
                    title: data[0].title,
                    icon: data[0].icon,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        });
    </script>
</div>
