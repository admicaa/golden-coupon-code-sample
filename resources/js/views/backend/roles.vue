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
          <v-toolbar-title>Roles</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <div class="flex-grow-1"></div>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on }">
              <v-btn
                color="primary"
                dark
                class="mb-2"
                v-on="on"
                v-if="$can(['create-roles'])"
              >New Item</v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12">
                      <v-text-field v-model="editedItem.name" label="Role Name"></v-text-field>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col
                      cols="4"
                      v-for="permission in permissions"
                      :key="'permission-'+editItem.id+'-'+permission.id"
                    >
                      <v-checkbox
                        :readonly="isRequired(permission)"
                        :input-value="permissionAdded(permission)"
                        @change="changePermission(...arguments,permission)"
                        :label="permission.name"
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
          v-if="$can(['edit-roles']) && item.name!=='super-admin'"
          class="mr-2"
          @click="editItem(item)"
        >edit</v-icon>
        <v-icon
          small
          v-if="$can(['delete-roles']) && item.name!=='super-admin'"
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

    allHeaders: [
      { text: "id", value: "id" },
      {
        text: "name",
        align: "left",
        sortable: false,
        value: "name"
      },

      {
        text: "Actions",
        value: "action",
        sortable: false,
        justify: "center",
        align: "center",
        permissions: ["edit-roles", "delete-roles"]
      }
    ],
    admins: [],
    editedIndex: -1,
    editedItem: {
      name: "",
      permissions: []
    },
    defaultItem: {
      name: "",
      permissions: []
    }
  }),
  computed: {
    ...mapGetters(["permissions"]),
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
    this.$store.dispatch("getPermissions").then(response => {
      this.getDataFromApi(this.options);
    });
    this.close();
  },
  mounted() {},
  methods: {
    isRequired(permission) {
      return this.editedItem.permissions.some(per => {
        return per.required && this.isPermissionFound(permission, per.required);
      });
    },
    permissionAdded(permission) {
      return this.isPermissionFound(permission, this.editedItem.permissions);
    },
    isPermissionFound(permission, permissions) {
      return permissions.some(per => {
        return (
          per.id === permission.id ||
          (per.required && this.isPermissionFound(permission, per.required))
        );
      });
    },
    changePermission(value, permission) {
      this.editedItem.permissions = this.editedItem.permissions.filter(per => {
        return permission.id !== per.id;
      });
      if (value) {
        this.editedItem.permissions.push(permission);
      }
    },
    getDataFromApi(options) {
      this.loading = true;
      this.$store.dispatch("getRoles", options).then(response => {
        this.admins = response.data.data;
        this.serverItemsLength = response.data.total;
        this.$nextTick(() => {
          this.loading = false;
        });
      });
    },
    initialize() {},
    editItem(item) {
      this.editedIndex = item.id;
      this.editedItem = Object.assign({}, item);

      this.$nextTick(() => {
        this.$nextTick(() => {
          this.dialog = true;
        });
      });
    },
    deleteItem(item) {
      const index = this.admins.indexOf(item);
      confirm("Are you sure you want to delete this item?") &&
        this.$store.dispatch("deleterole", item).then(response => {
          console.log(response);
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
        this.$store.dispatch("editRole", this.editedItem).then(response => {
          console.log(response);
          this.getDataFromApi(this.options);
        });
      } else {
        this.$store.dispatch("addNewRole", this.editedItem).then(response => {
          console.log(response);
          this.getDataFromApi(this.options);
        });
      }
      this.close();
    }
  }
};
</script>