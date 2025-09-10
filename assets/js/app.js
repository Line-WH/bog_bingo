const EXAMPLE_TASKS = [
    'Læs en bog under 200 sider','En bog af en debutforfatter','En prisvindende roman','En bog i en serie','En bog fra din barndom',
    'En bog sat i et andet land','En non-fiction','En bog udgivet i år','En grafisk roman/tegneserie','En dystopi',
    'En krimi/thriller','En klassiker','En bog anbefalet af en ven','En bog med et farvenavn i titlen','En bog om natur',
    'En bog af en dansk forfatter','En bog oversat til dansk','En lydbog','En bog med dyr på forsiden','En bog udgivet før 2000',
    'En fantasy','En sci‑fi','En selvhjælpsbog','En novellesamling','En bog du normalt ikke ville vælge'
];

const grid = document.getElementById('bingoGrid');
const totalCountEl = document.getElementById('totalCount');
const checkedCountEl = document.getElementById('checkedCount');
const progressBar = document.getElementById('progressBar');
const progressPctEl = document.getElementById('progressPct');
const resetBtn = document.getElementById('resetBtn');
const shareBtn = document.getElementById('shareBtn');

let tasks = [];

const loadTasks = () => {
    const saved = localStorage.getItem('bogbingo_tasks_simple');
    if (saved) {
        try { tasks = JSON.parse(saved); } catch { tasks = []; }
    }
    // Hvis gamle data indeholder "FRI FELT", fjern det og erstat med nyt felt
    if (tasks?.length) {
        tasks = tasks.filter(t => !String(t.text).toUpperCase().includes('FRI FELT'));
    }
    if (!tasks.length) {
        tasks = EXAMPLE_TASKS.map((t,i)=>({ id:i+1, text:t, done:false }));
    } else if (tasks.length < 25) {
        // Fyld op til 25, hvis der mangler efter oprydning
        const missing = EXAMPLE_TASKS.filter(x => !tasks.some(t => t.text === x))
            .slice(0, 25 - tasks.length)
            .map((t,i)=>({ id: tasks.length + i + 1, text:t, done:false }));
        tasks = tasks.concat(missing);
    } else if (tasks.length > 25) {
        tasks = tasks.slice(0,25);
    }
    saveTasks();
};

const saveTasks = () => localStorage.setItem('bogbingo_tasks_simple', JSON.stringify(tasks));

const updateProgress = () => {
    const total = tasks.length || 1;
    const done = tasks.filter(t=>t.done).length;
    const pct = Math.round((done/total)*100);
    totalCountEl.textContent = total;
    checkedCountEl.textContent = done;
    progressBar.style.width = pct+"%";
    progressBar.setAttribute('aria-valuenow', pct);
    progressPctEl.textContent = pct;
};

const toggleTask = (task, cell) => {
    task.done = !task.done;
    cell.classList.toggle('completed', task.done);
    saveTasks();
    updateProgress();
};

const renderGrid = () => {
    grid.innerHTML = '';
    tasks.forEach(task => {
        const cell = document.createElement('div');
        cell.className = 'bingo-cell'+(task.done?' completed':'');
        cell.setAttribute('role','button');
        cell.setAttribute('tabindex','0');
        cell.setAttribute('aria-pressed', task.done);

        const text = document.createElement('span');
        text.className = 'label-text';
        text.textContent = task.text;
        cell.appendChild(text);

        cell.addEventListener('click', ()=>{ toggleTask(task, cell); cell.setAttribute('aria-pressed', task.done); });
        cell.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' '){ e.preventDefault(); toggleTask(task, cell); cell.setAttribute('aria-pressed', task.done);} });

        grid.appendChild(cell);
    });
};

const resetAll = () => {
    tasks = tasks.map(t=>({...t, done:false}));
    saveTasks(); renderGrid(); updateProgress();
};

const shareProgress = () => {
    const total = tasks.length || 1;
    const done = tasks.filter(t=>t.done).length;
    const pct = Math.round((done/total)*100);
    const text = `Min Bogbingo-status: ${done}/${total} (${pct}%).`;
    if(navigator.share){ navigator.share({title:'Bogbingo', text}); }
    else { navigator.clipboard.writeText(text).then(()=>alert('Status kopieret til udklipsholder!')); }
};

loadTasks();
renderGrid();
updateProgress();
resetBtn.addEventListener('click', resetAll);
shareBtn.addEventListener('click', shareProgress);