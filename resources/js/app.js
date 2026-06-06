import flatpickr from 'flatpickr';
import Sortable from 'sortablejs';

window.flatpickr = flatpickr;
window.Sortable   = Sortable;

const disableWeekends = (date) => date.getDay() === 0 || date.getDay() === 6;

window.timelineApp = function (holidaysJson) {
    return {
        holidays: holidaysJson || [],
        fpStart: null,
        fpEnd: null,
        sortableInstance: null,

        init() {
            // Listen for modal-opened event from Livewire
            this.$wire.$on('modal-opened', ({ startDate, endDate }) => {
                setTimeout(() => this.initDatePickers(startDate, endDate), 30);
            });

            // Listen for auto-filled dates from dependency selection
            this.$wire.$on('dates-updated', ({ startDate, endDate }) => {
                if (this.fpStart) this.fpStart.setDate(startDate || null, false);
                if (this.fpEnd)   this.fpEnd.setDate(endDate   || null, false);
                if (startDate && this.fpEnd) this.fpEnd.set('minDate', startDate);
            });

            this.initSortable();

            // After every Livewire re-render, re-attach Flatpickr if it was destroyed by DOM morphing
            document.addEventListener('livewire:update', () => {
                setTimeout(() => {
                    this.initSortable();
                    if (!this.$wire.showModal) return;
                    const startEl = document.getElementById('fp-start-date');
                    const endEl   = document.getElementById('fp-end-date');
                    if ((startEl && !startEl._flatpickr) || (endEl && !endEl._flatpickr)) {
                        this.initDatePickers(
                            this.$wire.taskStartDate || '',
                            this.$wire.taskEndDate   || ''
                        );
                    }
                }, 100);
            });
        },

        initDatePickers(initialStart, initialEnd) {
            const startEl = document.getElementById('fp-start-date');
            const endEl   = document.getElementById('fp-end-date');

            if (startEl) {
                if (startEl._flatpickr) startEl._flatpickr.destroy();

                this.fpStart = flatpickr(startEl, {
                    dateFormat: 'Y-m-d',
                    defaultDate: initialStart || undefined,
                    disable: [disableWeekends, ...this.holidays],
                    onChange: (selectedDates, dateStr) => {
                        this.$wire.set('taskStartDate', dateStr);
                        if (this.fpEnd) this.fpEnd.set('minDate', dateStr);
                    }
                });
            }

            if (endEl) {
                if (endEl._flatpickr) endEl._flatpickr.destroy();

                this.fpEnd = flatpickr(endEl, {
                    dateFormat: 'Y-m-d',
                    defaultDate: initialEnd || undefined,
                    disable: [disableWeekends, ...this.holidays],
                    onChange: (selectedDates, dateStr) => {
                        this.$wire.set('taskEndDate', dateStr);
                    }
                });

                if (initialStart) this.fpEnd.set('minDate', initialStart);
            }
        },

        initSortable() {
            const tbody = document.getElementById('tasks-sortable');
            if (!tbody) return;

            if (this.sortableInstance) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }

            this.sortableInstance = Sortable.create(tbody, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-50',
                draggable: '[data-task-id]',
                onEnd: () => {
                    const ids = [...tbody.querySelectorAll('[data-task-id]')]
                        .map(el => parseInt(el.dataset.taskId));
                    this.$wire.reorderTasks(ids);
                }
            });
        },

        reloadHolidays() {
            fetch('/api/holidays-json', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => { this.holidays = data; });
        }
    };
};
