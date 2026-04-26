<template>
  <v-row justify="center">
    <v-data-table
      :headers="headers"
      :loading="loading"
      :items="admins"
      :server-items-length="serverItemsLength"
      :options.sync="options"
      class="elevation-1 col-12"
    >
      <template v-slot:top>
        <v-toolbar flat color="white">
          <v-toolbar-title>Admins</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <div class="flex-grow-1"></div>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on }">
              <v-btn
                color="primary"
                dark
                class="mb-2"
                v-on="on"
                v-if="$can(['create-admins'])"
              >New Item</v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="6" cols-sm="12">
                      <v-text-field v-model="editedItem.name" label="Admin Name"></v-text-field>
                    </v-col>
                    <v-col cols="6" cols-sm="12">
                      <v-text-field v-model="editedItem.email" label="Admin email"></v-text-field>
                    </v-col>
                    <v-col cols="12" cols-sm="12">
                      <v-text-field
                        type="password"
                        v-model="editedItem.password"
                        label="Admin password"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="4" v-for="role in roles" :key="'role'+role.id">
                      <v-checkbox
                        :readonly="role.name==='super-admin'"
                        :input-value="hasRole(role)"
                        @change="changeRole(...arguments,role)"
                        :label="role.name"
                      ></v-checkbox>
                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <div class="flex-grow-1"></div>
                <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                <v-btn color="blue darken-1" text @click="save">Save</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.action="{ item }">
        <v-icon
          small
          v-if="$can(['edit-admins']) && canBeEdited(item)"
          class="mr-2"
          @click="editItem(item)"
        >edit</v-icon>
        <v-icon
          small
          v-if="$can(['delete-admins']) && canBeDeleted(item)"
          @click="deleteItem(item)"
        >delete</v-icon>
      </template>
      <template v-slot:no-data>
        <v-btn color="primary" @click="initialize">Reset</v-btn>
      </template>
    </v-data-table>
  </v-row>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  data: () => ({
    dialog: false,
    loading: true,
    options: {},
    serverItemsLength: 0,
    roles: [],

    allHeaders: [
      {
        text: "name",
        align: "left",
        sortable: false,
        value: "name"
      },
      { text: "email", value: "email" },

      {
        text: "Actions",
        value: "action",
        sortable: false,
        permissions: ["edit-admins", "delete-admins"]
      }
    ],
    admins: [],
    editedIndex: -1,
    editedItem: {
      name: "",
      email: "",
      password: "",
      roles: []
    },
    defaultItem: {
      name: "",
      email: "",
      password: "",
      roles: []
    }
  }),
  computed: {
    ...mapGetters(["user"]),
    formTitle() {
      return this.editedIndex === -1 ? "New Item" : "Edit Item";
    },
    headers() {
      return this.allHeaders.filter(header => {
        if (!header.permissions) {
          return true;
        } else {
          return header.permissions.some(permission => {
            return this.$can([permission]);
          });
        }
      });
    }
  },
  watch: {
    dialog(val) {
      val || this.close();
    },
    options: {
      handler(newval) {
        this.getDataFromApi(newval);
      },
      deep: true
    }
  },
  created() {
    this.getDataFromApi(this.options);
    this.$store
      .dispatch("getRoles", {
        itemsPerPage: -1,
        onlyRoles: true
      })
      .then(response => {
        this.roles = response.data.data;
      });
  },
  methods: {
    hasRole(item) {
      return this.editedItem.roles.some(role => {
        return role.id === item.id;
      });
    },
    changeRole(value, item) {
      this.editedItem.roles = this.editedItem.roles.filter(role => {
        return role.id !== item.id;
      });
      if (value) {
        this.editedItem.roles.push(item);
      }
    },
    canBeDeleted(item) {
      return !item.roles.some(role => {
        return role.name === "super-admin";
      });
    },
    canBeEdited(item) {
      return this.canBeDeleted(item) || this.user.id === item.id;
    },
    getDataFromApi(options) {
      this.loading = true;
      this.$store.dispatch("getAdminUsers", options).then(response => {
        this.admins = response.data.data;
        this.serverItemsLength = response.data.total;
        this.$nextTick(() => {
          this.loading = false;
        });
      });
    },
    initialize() {},
    editItem(item) {
      this.editedIndex = this.admins.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    deleteItem(item) {
      const index = this.admins.indexOf(item);
      confirm("Are you sure you want to delete this item?") &&
        this.$store.dispatch("deleteAdmin", item).then(response => {
          this.getDataFromApi(this.options);
        });
    },
    close() {
      this.dialog = false;
      setTimeout(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      }, 300);
    },
    save() {
      if (this.editedIndex > -1) {
        this.$store.dispatch("updateAdmin", this.editedItem).then(response => {
          this.getDataFromApi(this.options);
        });
      } else {
        this.$store.dispatch("addNewAdmin", this.editedItem).then(response => {
          console.log(response);
          this.getDataFromApi(this.options);
        });
      }
      this.close();
    }
  }
};
</script>