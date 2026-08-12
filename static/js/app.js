(() => {
    const formatCurrency = (amount) =>
        new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2,
        }).format(amount);

    const attachHomeMovies = () => {
        const movieGrid = document.getElementById('movie-grid');
        const searchInput = document.getElementById('movie-search');
        const locationFilter = document.getElementById('location-filter');

        if (!movieGrid || !searchInput || !locationFilter) {
            return;
        }

        const apiUrl = movieGrid.dataset.api;

        const renderMovies = (movies) => {
            movieGrid.innerHTML = '';

            if (!movies.length) {
                movieGrid.innerHTML = '<div class="col-12"><div class="alert alert-info">No movies found for this filter.</div></div>';
                return;
            }

            movies.forEach((movie) => {
                const col = document.createElement('div');
                col.className = 'col-md-3 mb-4';
                col.innerHTML = `
                    <article class="movie-card h-100">
                        <img src="${movie.poster_url}" alt="${movie.title} poster" class="movie-poster" loading="lazy" />
                        <div class="p-3">
                            <h3 class="h5 mb-1">${movie.title}</h3>
                            <p class="small text-secondary mb-1">${movie.genre}</p>
                            <p class="small text-secondary mb-2">${movie.duration} · ${movie.language}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-info-subtle text-info">₹${Number(movie.ticket_price).toFixed(0)}</span>
                                <a class="btn btn-danger btn-sm" href="movie_details.php?id=${movie.id}">View Shows</a>
                            </div>
                        </div>
                    </article>
                `;
                movieGrid.appendChild(col);
            });
        };

        const fetchMovies = async () => {
            const params = new URLSearchParams();
            const search = searchInput.value.trim();
            const location = locationFilter.value;

            if (search) params.append('search', search);
            if (location) params.append('location', location);

            try {
                const res = await fetch(`${apiUrl}?${params.toString()}`);
                const data = await res.json();
                if (data.success) {
                    renderMovies(data.movies || []);
                }
            } catch (err) {
                movieGrid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Unable to load movies right now.</div></div>';
            }
        };

        let searchTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchMovies, 250);
        });
        locationFilter.addEventListener('change', fetchMovies);
    };

    const attachBooking = () => {
        const bookingRoot = document.querySelector('[data-page="booking"]');
        if (!bookingRoot) {
            return;
        }

        const showId = bookingRoot.dataset.showId;
        const seatApi = bookingRoot.dataset.seatApi;
        const bookingApi = bookingRoot.dataset.bookingApi;
        const checkoutUrl = bookingRoot.dataset.checkoutUrl;

        const seatGrid = document.getElementById('seat-grid');
        const selectedSeatsDisplay = document.getElementById('selected-seats-display');
        const basePriceEl = document.getElementById('base-price');
        const gstPriceEl = document.getElementById('gst-price');
        const totalPriceEl = document.getElementById('total-price');
        const reserveBtn = document.getElementById('reserve-seats-btn');
        const seatError = document.getElementById('seat-error');

        const priceText = (document.body.textContent.match(/Base Price:\s*₹([\d,.]+)/) || [])[1] || '0';
        const baseTicketPrice = Number(priceText.replace(/,/g, '')) || 0;
        const state = { occupied: [], selected: [] };

        const rows = ['A', 'B', 'C', 'D', 'E'];
        const seatsPerRow = 8;

        const sortSeats = (seats) => seats.sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));

        const updateSummary = () => {
            const seatCount = state.selected.length;
            const base = seatCount * baseTicketPrice;
            const gst = base * 0.18;
            const total = base + gst;

            selectedSeatsDisplay.textContent = seatCount ? state.selected.join(', ') : 'None';
            basePriceEl.textContent = formatCurrency(base);
            gstPriceEl.textContent = formatCurrency(gst);
            totalPriceEl.textContent = formatCurrency(total);
            reserveBtn.disabled = seatCount === 0;
        };

        const renderSeats = () => {
            seatGrid.innerHTML = '';
            rows.forEach((row) => {
                const rowEl = document.createElement('div');
                rowEl.className = 'seat-row';

                const rowLabel = document.createElement('span');
                rowLabel.className = 'row-label';
                rowLabel.textContent = row;
                rowEl.appendChild(rowLabel);

                for (let i = 1; i <= seatsPerRow; i += 1) {
                    const seatNo = `${row}${i}`;
                    const seatBtn = document.createElement('button');
                    seatBtn.type = 'button';
                    seatBtn.className = 'seat';
                    seatBtn.textContent = i;
                    seatBtn.dataset.seatNo = seatNo;

                    if (state.occupied.includes(seatNo)) {
                        seatBtn.classList.add('occupied');
                        seatBtn.disabled = true;
                    } else if (state.selected.includes(seatNo)) {
                        seatBtn.classList.add('selected');
                    } else {
                        seatBtn.classList.add('available');
                    }

                    seatBtn.addEventListener('click', () => {
                        const idx = state.selected.indexOf(seatNo);
                        if (idx >= 0) {
                            state.selected.splice(idx, 1);
                        } else {
                            state.selected.push(seatNo);
                            sortSeats(state.selected);
                        }
                        renderSeats();
                        updateSummary();
                    });

                    rowEl.appendChild(seatBtn);
                }

                seatGrid.appendChild(rowEl);
            });
        };

        const loadOccupiedSeats = async () => {
            const res = await fetch(`${seatApi}?show_id=${encodeURIComponent(showId)}`);
            const data = await res.json();
            state.occupied = (data.occupied_seats || []).map((x) => String(x));
            renderSeats();
            updateSummary();
        };

        reserveBtn.addEventListener('click', async () => {
            seatError.textContent = '';
            reserveBtn.disabled = true;

            try {
                const res = await fetch(bookingApi, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        show_id: Number(showId),
                        seats: state.selected,
                    }),
                });
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'Seat reservation failed.');
                }

                const target = data.checkout_url || `${checkoutUrl}?reservation=${encodeURIComponent(data.reservation_token)}`;
                window.location.href = target;
            } catch (error) {
                seatError.textContent = error.message;
                reserveBtn.disabled = false;
                await loadOccupiedSeats();
            }
        });

        loadOccupiedSeats().catch(() => {
            seatError.textContent = 'Unable to load seat map.';
        });
    };

    const attachCheckout = () => {
        const root = document.getElementById('checkout-root');
        const payBtn = document.getElementById('pay-now-btn');
        const errorEl = document.getElementById('checkout-error');

        if (!root || !payBtn) {
            return;
        }

        const keyId = root.dataset.keyId;
        const amount = Number(root.dataset.amount || '0');
        const verifyApi = root.dataset.verifyApi;
        const reservationToken = root.dataset.reservationToken;

        if (!keyId || !window.Razorpay) {
            return;
        }

        payBtn.addEventListener('click', () => {
            errorEl.textContent = '';
            const options = {
                key: keyId,
                amount,
                currency: 'INR',
                name: 'CinePass',
                description: 'Movie Ticket Booking',
                handler: async (response) => {
                    try {
                        const res = await fetch(verifyApi, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                reservation_token: reservationToken,
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id || '',
                                razorpay_signature: response.razorpay_signature || '',
                            }),
                        });

                        const data = await res.json();
                        if (!data.success) {
                            throw new Error(data.message || 'Payment verification failed.');
                        }

                        window.location.href = data.redirect_url;
                    } catch (error) {
                        errorEl.textContent = error.message;
                    }
                },
                theme: { color: '#e11d48' },
                modal: {
                    ondismiss: () => {
                        errorEl.textContent = 'Payment popup closed before completion.';
                    },
                },
            };

            const razorpay = new window.Razorpay(options);
            razorpay.open();
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        attachHomeMovies();
        attachBooking();
        attachCheckout();
    });
})();
