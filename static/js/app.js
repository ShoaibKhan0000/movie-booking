(() => {
    const movies = [
        {
            id: 'kalki-2898',
            title: 'Project Kalki',
            genre: 'Sci-Fi · Action',
            language: 'Hindi, Telugu',
            price: 320,
            image: 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=700&q=80'
        },
        {
            id: 'shadow-protocol',
            title: 'Shadow Protocol',
            genre: 'Thriller · Mystery',
            language: 'Hindi, English',
            price: 260,
            image: 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=700&q=80'
        },
        {
            id: 'oceanic-heist',
            title: 'Oceanic Heist',
            genre: 'Crime · Adventure',
            language: 'English',
            price: 290,
            image: 'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?auto=format&fit=crop&w=700&q=80'
        },
        {
            id: 'satrangi-ishq',
            title: 'Satrangi Ishq',
            genre: 'Romance · Drama',
            language: 'Hindi',
            price: 220,
            image: 'https://images.unsplash.com/photo-1626814026160-2237a95fc5a0?auto=format&fit=crop&w=700&q=80'
        },
        {
            id: 'iron-raaga',
            title: 'Iron Raaga',
            genre: 'Action · Musical',
            language: 'Tamil, Hindi',
            price: 250,
            image: 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?auto=format&fit=crop&w=700&q=80'
        },
        {
            id: 'quantum-yatra',
            title: 'Quantum Yatra',
            genre: 'Sci-Fi · Fantasy',
            language: 'Hindi, Kannada',
            price: 310,
            image: 'https://images.unsplash.com/photo-1505685296765-3a2736de412f?auto=format&fit=crop&w=700&q=80'
        }
    ];

    const occupiedByMovie = {
        'kalki-2898': ['A4', 'A5', 'B1', 'C7', 'D3', 'E6'],
        'shadow-protocol': ['A1', 'B2', 'B6', 'C3', 'D8'],
        'oceanic-heist': ['A3', 'A7', 'C2', 'D4', 'E1', 'E2'],
        'satrangi-ishq': ['B4', 'B5', 'C6', 'D1', 'E8'],
        'iron-raaga': ['A2', 'B8', 'C1', 'D2', 'D7', 'E4'],
        'quantum-yatra': ['A6', 'B3', 'C4', 'C5', 'E3', 'E7']
    };

    const state = {
        selectedMovie: null,
        selectedSeats: [],
        occupiedSeats: [],
        weeklyTrend: [108, 136, 122, 154, 176, 168, 201],
        baseKpi: {
            tickets: 4276,
            revenue: 1284000,
            users: 2490
        }
    };

    const dom = {
        movieGrid: document.getElementById('movie-grid'),
        bookingSection: document.getElementById('booking-section'),
        selectedMovieHeading: document.getElementById('selected-movie-heading'),
        summaryMovieTitle: document.getElementById('summary-movie-title'),
        summaryDatetime: document.getElementById('summary-datetime'),
        showtimeSelect: document.getElementById('showtime-select'),
        seatGrid: document.getElementById('seat-grid'),
        selectedSeatsDisplay: document.getElementById('selected-seats-display'),
        basePrice: document.getElementById('base-price'),
        taxFee: document.getElementById('tax-fee'),
        totalPrice: document.getElementById('total-price'),
        checkoutBtn: document.getElementById('checkout-btn'),
        checkoutNote: document.getElementById('checkout-note'),
        searchDesktop: document.getElementById('movie-search'),
        searchMobile: document.getElementById('movie-search-mobile'),
        kpiTickets: document.getElementById('kpi-tickets'),
        kpiRevenue: document.getElementById('kpi-revenue'),
        kpiUsers: document.getElementById('kpi-users')
    };

    const formatCurrency = (value) =>
        new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(value);

    const formatWholeCurrency = (value) =>
        new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(value);

    const getSearchValue = () => (dom.searchDesktop?.value || dom.searchMobile?.value || '').trim().toLowerCase();

    const renderMovieCards = () => {
        const query = getSearchValue();
        const visibleMovies = movies.filter((movie) =>
            [movie.title, movie.genre, movie.language].join(' ').toLowerCase().includes(query)
        );

        dom.movieGrid.innerHTML = '';

        if (!visibleMovies.length) {
            dom.movieGrid.innerHTML = '<p class="col-span-full rounded-xl border border-white/10 bg-slateCard/70 p-6 text-center text-slate-300">No matching movies found.</p>';
            return;
        }

        visibleMovies.forEach((movie) => {
            const card = document.createElement('article');
            card.className = 'movie-card';
            card.setAttribute('role', 'button');
            card.setAttribute('tabindex', '0');
            card.dataset.movieId = movie.id;
            card.innerHTML = `
                <img src="${movie.image}" alt="${movie.title} poster" class="movie-poster" loading="lazy" />
                <div class="p-4">
                    <h3 class="text-lg font-semibold">${movie.title}</h3>
                    <p class="mt-1 text-xs text-slate-300">${movie.genre}</p>
                    <p class="mt-1 text-xs text-slate-400">${movie.language}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="rounded-full border border-neonBlue/40 bg-neonBlue/10 px-3 py-1 text-xs text-neonBlue">${formatCurrency(movie.price)}</span>
                        <button class="rounded-full bg-cineRed px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">Select</button>
                    </div>
                </div>
            `;

            const selectMovie = () => setSelectedMovie(movie.id);
            card.addEventListener('click', selectMovie);
            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    selectMovie();
                }
            });
            dom.movieGrid.appendChild(card);
        });
    };

    const renderSeatLayout = () => {
        const rows = ['A', 'B', 'C', 'D', 'E'];
        const seatsPerRow = 8;

        dom.seatGrid.innerHTML = '';

        rows.forEach((row) => {
            const rowEl = document.createElement('div');
            rowEl.className = 'seat-row';

            const label = document.createElement('span');
            label.className = 'row-label';
            label.textContent = row;
            rowEl.appendChild(label);

            for (let seatNum = 1; seatNum <= seatsPerRow; seatNum += 1) {
                const seatLabel = `${row}${seatNum}`;
                const isOccupied = state.occupiedSeats.includes(seatLabel);
                const isSelected = state.selectedSeats.includes(seatLabel);

                const seat = document.createElement('button');
                seat.type = 'button';
                seat.className = `seat ${isOccupied ? 'occupied' : isSelected ? 'selected' : 'available'}`;
                seat.textContent = seatNum;
                seat.dataset.seatLabel = seatLabel;

                if (isOccupied) {
                    seat.disabled = true;
                    seat.setAttribute('aria-disabled', 'true');
                    seat.title = `${seatLabel} occupied`;
                } else {
                    seat.title = `${seatLabel} available`;
                    seat.addEventListener('click', () => toggleSeat(seatLabel));
                }

                rowEl.appendChild(seat);
            }

            dom.seatGrid.appendChild(rowEl);
        });
    };

    const updateBookingSummary = () => {
        const seatCount = state.selectedSeats.length;
        const pricePerSeat = state.selectedMovie?.price || 0;
        const base = seatCount * pricePerSeat;
        const tax = base * 0.18;
        const total = base + tax;

        dom.selectedSeatsDisplay.textContent = seatCount ? state.selectedSeats.join(', ') : 'None';
        dom.basePrice.textContent = formatCurrency(base);
        dom.taxFee.textContent = formatCurrency(tax);
        dom.totalPrice.textContent = formatCurrency(total);

        dom.checkoutBtn.disabled = seatCount === 0 || !state.selectedMovie;
        dom.checkoutNote.textContent = dom.checkoutBtn.disabled
            ? 'Select at least one seat to proceed with checkout.'
            : 'Great! You can now continue to secure payment.';

        updateKpiCards(seatCount, total);
    };

    const updateKpiCards = (addedTickets = 0, addedRevenue = 0) => {
        const tickets = state.baseKpi.tickets + addedTickets;
        const revenue = state.baseKpi.revenue + Math.round(addedRevenue);
        const users = state.baseKpi.users + (addedTickets > 0 ? 1 : 0);

        dom.kpiTickets.textContent = new Intl.NumberFormat('en-IN').format(tickets);
        dom.kpiRevenue.textContent = formatWholeCurrency(revenue);
        dom.kpiUsers.textContent = new Intl.NumberFormat('en-IN').format(users);
    };

    const toggleSeat = (seatLabel) => {
        if (!state.selectedMovie) {
            return;
        }

        const index = state.selectedSeats.indexOf(seatLabel);
        if (index >= 0) {
            state.selectedSeats.splice(index, 1);
        } else {
            state.selectedSeats.push(seatLabel);
            state.selectedSeats.sort((a, b) => {
                const rowDiff = a.charCodeAt(0) - b.charCodeAt(0);
                if (rowDiff !== 0) {
                    return rowDiff;
                }
                return Number(a.slice(1)) - Number(b.slice(1));
            });
        }

        renderSeatLayout();
        updateBookingSummary();
    };

    const setSelectedMovie = (movieId) => {
        const movie = movies.find((item) => item.id === movieId);
        if (!movie) {
            return;
        }

        state.selectedMovie = movie;
        state.selectedSeats = [];
        state.occupiedSeats = occupiedByMovie[movieId] || [];

        dom.selectedMovieHeading.textContent = `${movie.title} · ${movie.genre}`;
        dom.summaryMovieTitle.textContent = movie.title;
        dom.summaryDatetime.textContent = `Date & Time: ${dom.showtimeSelect.value}`;

        dom.bookingSection.classList.remove('hidden');
        dom.bookingSection.classList.add('fade-slide');

        renderSeatLayout();
        updateBookingSummary();

        window.requestAnimationFrame(() => {
            dom.bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    };

    const bindSearchSync = () => {
        const syncSearchInput = (source, target) => {
            if (!source || !target) {
                return;
            }
            source.addEventListener('input', () => {
                target.value = source.value;
                renderMovieCards();
            });
        };

        syncSearchInput(dom.searchDesktop, dom.searchMobile);
        syncSearchInput(dom.searchMobile, dom.searchDesktop);
    };

    const bindEvents = () => {
        dom.showtimeSelect.addEventListener('change', () => {
            dom.summaryDatetime.textContent = `Date & Time: ${dom.showtimeSelect.value}`;
        });

        dom.checkoutBtn.addEventListener('click', () => {
            if (!state.selectedMovie || !state.selectedSeats.length) {
                return;
            }

            alert(`Booking confirmed for ${state.selectedMovie.title} (${dom.showtimeSelect.value})\nSeats: ${state.selectedSeats.join(', ')}`);
        });
    };

    const initialize = () => {
        renderMovieCards();
        updateKpiCards();
        bindSearchSync();
        bindEvents();

        window.cinePassState = {
            weeklyTrend: [...state.weeklyTrend]
        };
    };

    document.addEventListener('DOMContentLoaded', initialize);
})();
