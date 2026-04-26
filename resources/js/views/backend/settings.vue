<template>
  <v-row justify="center">
    <v-card width="90%">
      <v-img
        class="mx-auto"
        centered
        width="200px"
        height="200px"
        :src="$store.getters['user'].image"
      ></v-img>
      <v-card-title>Profile Setting</v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="6" cols-sm="12">
            <v-text-field v-model="editedUser.name" label="Admin Name"></v-text-field>
          </v-col>
          <v-col cols="6" cols-sm="12">
            <v-text-field v-model="editedUser.email" label="Admin email"></v-text-field>
          </v-col>
          <v-col cols="6" cols-sm="12" v-if="$can(['edit-his-password'])">
            <v-text-field type="password" v-model="editedUser.password" label="Admin password"></v-text-field>
          </v-col>
          <v-file-input
            accept="image/png, image/jpeg, image/bmp"
            placeholder="Pick an avatar"
            prepend-icon="mdi-camera"
            label="Avatar"
            @change="setAvatar"
          ></v-file-input>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <div class="flex-grow-1"></div>
        <v-btn @click.prevent="saveData" color="primary">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-row>
</template>


<script>
export default {
  data() {
    return {
      editedUser: {}
    };
  },
  computed: {},
  props: {},
  watch: {},
  methods: {
    setAvatar(file) {
      this.editedUser.avatar = file;
    },
    saveData() {
      var form_data = new FormData();

      for (var key in this.editedUser) {
        let value = this.editedUser[key] ? this.editedUser[key] : "";
        if (value) {
          form_data.append(key, value);
        }
      }

      this.$store.dispatch("updateAdmin", form_data).then(response => {
        if (this.editedUser.avatar) {
          window.location.reload();
        }
      });
    }
  },
  components: {},
  created() {
    this.editedUser = Object.assign(
      {},
      {
        id: this.$store.getters["user"].id,
        name: this.$store.getters["user"].name,
        email: this.$store.getters["user"].email,
        password: this.$store.getters["user"].password
      }
    );
  },
  mounted() {}
};
</script>


/******************* 
*
*
*
*    Ahmed Ali Tahbet
*
*
 ********************/