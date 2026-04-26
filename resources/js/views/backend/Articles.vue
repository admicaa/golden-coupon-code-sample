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
          <v-toolbar-title>Artilces</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <div class="flex-grow-1"></div>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on }">
              <v-btn
                color="primary"
                dark
                class="mb-2"
                v-on="on"
                v-if="$can(['create-articles'])"
              >New Item</v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12" cols-sm="12">
                      <v-text-field v-model="editedItem.name" label="Article Name"></v-text-field>
                    </v-col>
                    <v-col cols="12" cols-sm="12">
                      <v-textarea
                        :auto-grow="true"
                        :clearable="true"
                        v-model="editedItem.body"
                        label="body"
                      ></v-textarea>
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
      <template v-slot:item.auther_information="{ item }">{{ item.auther_information.name }}</template>
      <template v-slot:item.action="{ item }">
        <v-icon
          small
          v-if="$can(['edit-all-articles']) || canEditArticle(item)"
          class="mr-2"
          @click="editItem(item)"
        >edit</v-icon>
        <v-icon
          small
          v-if="$can(['delete-all-articles']) || canBeDeleted(item)"
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
      { text: "content", value: "body" },
      { text: "creator", sortable: false, value: "auther_information" },

      {
        text: "Actions",
        value: "action",
        sortable: false,
        permissions: [
          "edit-his-articles",
          "edit-all-articles",
          "delete-his-articles",
          "delete-all-articles"
        ]
      }
    ],
    admins: [],
    editedIndex: -1,
    editedItem: {
      name: "",
      body: ""
    },
    defaultItem: {
      name: "",
      body: ""
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
  },
  methods: {
    canBeDeleted(item) {
      return (
        this.$can(["delete-his-articles"]) && this.user.id === item.admin_id
      );
    },
    canEditArticle(item) {
      return this.$can(["edit-his-articles"]) && this.user.id === item.admin_id;
    },
    getDataFromApi(options) {
      this.loading = true;
      this.$store.dispatch("getArticles", options).then(response => {
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
      confirm("Are you sure you want to delete this item?") &&
        this.$store.dispatch("deleteArticle", item).then(response => {
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
        this.$store
          .dispatch("updateArticle", this.editedItem)
          .then(response => {
            this.getDataFromApi(this.options);
          });
      } else {
        this.$store.dispatch("addArticle", this.editedItem).then(response => {
          console.log(response);
          this.getDataFromApi(this.options);
        });
      }
      this.close();
    }
  }
};
</script>