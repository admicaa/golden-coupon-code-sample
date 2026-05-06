<template>
  <v-row justify="center">
    <v-data-table
      :headers="headers"
      :loading="loading"
      :items="articles"
      :server-items-length="serverItemsLength"
      :options.sync="options"
      class="elevation-1 col-12"
    >
      <template v-slot:top>
        <v-toolbar flat color="white">
          <v-toolbar-title>Articles</v-toolbar-title>
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
                        label="Description"
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
      <template v-slot:item.action="{ item }">
        <v-icon
          small
          v-if="$can(['edit-articles'])"
          class="mr-2"
          @click="editItem(item)"
        >edit</v-icon>
        <v-icon
          small
          v-if="$can(['delete-articles'])"
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
export default {
  data: () => ({
    dialog: false,
    loading: true,
    options: {},
    serverItemsLength: 0,

    allHeaders: [
      {
        text: "name",
        align: "left",
        sortable: false,
        value: "name"
      },
      { text: "description", value: "body" },

      {
        text: "Actions",
        value: "action",
        sortable: false,
        permissions: ["edit-articles", "delete-articles"]
      }
    ],
    articles: [],
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
    getDataFromApi(options) {
      this.loading = true;
      this.$store.dispatch("getArticles", options).then(response => {
        this.articles = response.data.data;
        this.serverItemsLength = response.data.total;
        this.$nextTick(() => {
          this.loading = false;
        });
      });
    },
    initialize() {},
    editItem(item) {
      this.editedIndex = this.articles.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },
    deleteItem(item) {
      confirm("Are you sure you want to delete this item?") &&
        this.$store.dispatch("deleteArticle", item).then(() => {
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
          .then(() => {
            this.getDataFromApi(this.options);
          });
      } else {
        this.$store.dispatch("addArticle", this.editedItem).then(() => {
          this.getDataFromApi(this.options);
        });
      }
      this.close();
    }
  }
};
</script>
