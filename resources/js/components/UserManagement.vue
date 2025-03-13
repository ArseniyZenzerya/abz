<template>
    <div class="container mt-4">
        <h2 class="mb-3">Users</h2>
        <table class="table table-bordered">
            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Position</th>
                <th>Photo</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.phone }}</td>
                <td>{{ user.position ? user.position : 'N/A' }}</td>
                <td><img :src="user.photo" alt="User Photo" class="user-photo" /></td>
            </tr>
            </tbody>
        </table>
        <button v-if="hasMore" @click="loadMore" class="btn btn-primary">Show more</button>

        <h2 class="mt-4">Generate Registration Token</h2>
        <button @click="fetchToken" class="btn btn-warning">Generate Token</button>
        <p v-if="registrationToken" class="mt-2"><strong>Token:</strong> {{ registrationToken }}</p>

        <h2 class="mt-4">Add New User</h2>
        <small v-if="errors.token" class="text-danger">{{ errors.token }}</small>
        <form @submit.prevent="addUser" class="mt-3">
            <div class="form-group">
                <input v-model="newUser.name" class="form-control" placeholder="Name" required />
                <small v-if="errors.name" class="text-danger">{{ errors.name }}</small>
            </div>
            <div class="form-group">
                <input v-model="newUser.email" type="email" class="form-control" placeholder="Email" required />
                <small v-if="errors.email" class="text-danger">{{ errors.email }}</small>
            </div>
            <div class="form-group">
                <input v-model="newUser.phone" class="form-control" placeholder="Phone" required />
                <small v-if="errors.phone" class="text-danger">{{ errors.phone }}</small>
            </div>
            <div class="form-group">
                <select v-model="newUser.position_id" class="form-control" required>
                    <option value="" disabled>Select Position</option>
                    <option v-for="position in positions" :key="position.id" :value="position.id">
                        {{ position.name }}
                    </option>
                </select>
                <small v-if="errors.position_id" class="text-danger">{{ errors.position_id }}</small>
            </div>
            <div class="form-group">
                <input type="file" @change="handleFileUpload" class="form-control" required />
                <small v-if="errors.photo" class="text-danger">{{ errors.photo }}</small>
            </div>
            <button type="submit" class="btn btn-success">Add User</button>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            users: [],
            positions: [],
            page: 1,
            perPage: 6,
            totalPages: 1,
            newUser: {
                name: '',
                email: '',
                phone: '',
                position_id: '',
                photo: null,
            },
            registrationToken: '',
            errors: {},
        };
    },
    computed: {
        hasMore() {
            return this.page < this.totalPages;
        }
    },
    methods: {
        async fetchUsers() {
            try {
                const response = await axios.get(`/api/users?page=${this.page}&count=${this.perPage}`);
                if (response.data && response.data.users) {
                    const users = response.data.users.map(user => ({
                        ...user,
                        position: user.position || 'N/A'
                    }));
                    this.users = [...this.users, ...users];
                    this.totalPages = response.data.total_pages;
                }
            } catch (error) {
                console.error('Error fetching users:', error);
            }
        },
        async fetchPositions() {
            try {
                const response = await axios.get('/api/positions');
                if (response.data && response.data.positions) {
                    this.positions = response.data.positions;
                }
            } catch (error) {
                console.error('Error fetching positions:', error);
            }
        },
        async fetchToken() {
            try {
                this.errors = {};
                const response = await axios.get('/api/token');
                this.registrationToken = response.data.token;
            } catch (error) {
                this.errors.token = error.response?.data?.message || 'Failed to fetch token';
            }
        },
        loadMore() {
            this.page++;
            this.fetchUsers();
        },
        handleFileUpload(event) {
            this.newUser.photo = event.target.files[0];
        },
        async addUser() {
            try {
                this.errors = {};

                if (!this.registrationToken) {
                    this.errors.token = 'Token is required.';
                    return;
                }

                const existingUser = this.users.find(user => user.email === this.newUser.email);
                if (existingUser) {
                    this.errors.email = 'User with this email already exists.';
                    return;
                }

                let formData = new FormData();
                formData.append('name', this.newUser.name);
                formData.append('email', this.newUser.email);
                formData.append('phone', this.newUser.phone);
                formData.append('position_id', this.newUser.position_id);
                formData.append('photo', this.newUser.photo);

                const response = await axios.post('/api/users', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Authorization': `Bearer ${this.registrationToken}`
                    }
                });

                alert('User added successfully!');

                if (this.users.length < this.perPage * this.page) {
                    const newUser = {
                        ...response.data.user,
                        position: response.data.user.position ? response.data.user.position.name : 'N/A'
                    };
                    this.users.push(newUser);
                }

                this.totalPages = Math.ceil((this.users.length + 1) / this.perPage);

                this.newUser = { name: '', email: '', phone: '', position_id: '', photo: null };

            } catch (error) {
                if (error.response && error.response.data.errors) {
                    this.errors = error.response.data.errors;
                } else if (error.response && error.response.data.message) {
                    this.errors.token = error.response.data.message || 'An error occurred';
                }
                console.error('Error adding user:', error);
            }
        }
    },
    mounted() {
        this.fetchUsers();
        this.fetchPositions();
    }
};
</script>

<style>
.user-photo {
    width: 50px;
    height: 50px;
    object-fit: cover;
}
</style>
